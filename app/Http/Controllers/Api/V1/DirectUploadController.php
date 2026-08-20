<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VideoResource;
use App\Jobs\ProcessUploadedVideo;
use App\Models\UploadSession;
use App\Models\Video;
use App\Services\ContentCache;
use App\Services\R2UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DirectUploadController extends Controller
{
    public function initiate(Request $request, R2UploadService $uploads): JsonResponse
    {
        $this->authorize('create', Video::class);

        $data = $request->validate([
            'extension' => ['required', 'string', Rule::in(['mp4', 'jpg', 'jpeg', 'png', 'webp'])],
            'content_type' => ['required', 'string', Rule::in(['video/mp4', 'image/jpeg', 'image/png', 'image/webp'])],
        ]);

        if (! $this->matchesContentType($data['extension'], $data['content_type'])) {
            throw ValidationException::withMessages([
                'content_type' => 'Dosya uzantısı ve içerik türü birbiriyle eşleşmelidir.',
            ]);
        }

        $upload = $uploads->createUpload($data['extension'], $data['content_type']);
        $session = UploadSession::query()->create([
            'user_id' => $request->user('sanctum')->id,
            'object_key' => $upload['key'],
            'content_type' => $data['content_type'],
            'extension' => $data['extension'],
            'status' => UploadSession::STATUS_PENDING,
            'expires_at' => now()->addSeconds($upload['expires_in']),
        ]);

        // Optional for newer clients; the established Android fields remain unchanged.
        $upload['session_id'] = $session->id;

        return response()->json(['upload' => $upload], 201);
    }

    public function complete(Request $request, R2UploadService $uploads): JsonResponse
    {
        $this->authorize('create', Video::class);

        $data = $request->validate([
            'video_key' => ['required', 'string', 'regex:/^videos\/[0-9a-f-]{36}\.mp4$/i'],
            'thumbnail_key' => ['nullable', 'string', 'regex:/^thumbnails\/[0-9a-f-]{36}\.(jpg|jpeg|png|webp)$/i'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'status' => ['required', Rule::in(['public', 'private', 'unlisted', 'draft'])],
            'license' => ['nullable', Rule::in(['standard', 'creative_commons'])],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:50', 'distinct'],
            'is_short' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
        ]);

        $user = $request->user('sanctum');
        $sessions = $this->sessionsFor($user->id, $data);

        if ($sessions['video']->status === UploadSession::STATUS_COMPLETED) {
            return $this->completedResponse($user->id, $data);
        }

        $this->ensureSessionsCanComplete($sessions);

        $missing = [];
        foreach (['video' => 'video_key', 'thumbnail' => 'thumbnail_key'] as $type => $key) {
            if (isset($sessions[$type]) && ! $uploads->exists($data[$key])) {
                $missing[$key] = 'Yüklenen medya dosyası bulunamadı veya yükleme süresi doldu.';
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages($missing);
        }

        $result = DB::transaction(function () use ($data, $user): array {
            $sessions = $this->sessionsFor($user->id, $data, true);

            if ($sessions['video']->status === UploadSession::STATUS_COMPLETED) {
                return ['video' => $this->completedVideo($user->id, $data), 'created' => false];
            }

            $this->ensureSessionsCanComplete($sessions);

            $video = Video::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'video_path' => $data['video_key'],
                'thumbnail' => $data['thumbnail_key'] ?? null,
                'processing_status' => 'pending',
                'channel_name' => $user->name,
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'status' => $data['status'],
                'license' => $data['license'] ?? $user->default_video_license ?: 'standard',
                'tags' => collect($data['tags'] ?? [])
                    ->map(fn (string $tag) => trim($tag))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
                'is_short' => (bool) ($data['is_short'] ?? false),
                'is_premium' => (bool) ($data['is_premium'] ?? false),
                'views' => 0,
                'duration' => 0,
            ]);

            foreach ($sessions as $session) {
                $session->update([
                    'status' => UploadSession::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            }

            return ['video' => $video, 'created' => true];
        });

        if ($result['created']) {
            ProcessUploadedVideo::dispatch($result['video']);
            ContentCache::flush();
        }

        $video = $result['video']->load(['user', 'category'])->loadCount(['likes', 'comments']);

        return (new VideoResource($video))
            ->additional(['message' => $video->is_short
                ? 'Shorts yüklemesi alındı; medya işleniyor.'
                : 'Video yüklemesi alındı; medya işleniyor.'])
            ->response()
            ->setStatusCode($result['created'] ? 201 : 200);
    }

    private function matchesContentType(string $extension, string $contentType): bool
    {
        return match (strtolower($extension)) {
            'mp4' => $contentType === 'video/mp4',
            'jpg', 'jpeg' => $contentType === 'image/jpeg',
            'png' => $contentType === 'image/png',
            'webp' => $contentType === 'image/webp',
            default => false,
        };
    }

    /** @return array{video: UploadSession, thumbnail?: UploadSession} */
    private function sessionsFor(int $userId, array $data, bool $lock = false): array
    {
        $sessions = [];

        foreach (['video' => 'video_key', 'thumbnail' => 'thumbnail_key'] as $type => $key) {
            if (! filled($data[$key] ?? null)) {
                continue;
            }

            $query = UploadSession::query()->forOwnerAndKey($userId, $data[$key]);
            if ($lock) {
                $query->lockForUpdate();
            }

            $session = $query->first();
            if (! $session) {
                throw ValidationException::withMessages([
                    $key => 'Bu yükleme oturumuna erişim izniniz yok veya oturum bulunamadı.',
                ]);
            }

            $sessions[$type] = $session;
        }

        return $sessions;
    }

    /** @param array{video: UploadSession, thumbnail?: UploadSession} $sessions */
    private function ensureSessionsCanComplete(array $sessions): void
    {
        $errors = [];

        foreach ($sessions as $type => $session) {
            $field = $type === 'video' ? 'video_key' : 'thumbnail_key';

            if ($session->status !== UploadSession::STATUS_PENDING) {
                $errors[$field] = 'Bu yükleme oturumu artık tamamlanamaz.';
                continue;
            }

            // A brief grace period covers an upload that finished just before
            // presigned URL expiry while keeping the session short-lived.
            if ($session->expires_at->addSeconds(120)->isPast()) {
                $session->update(['status' => UploadSession::STATUS_EXPIRED]);
                $errors[$field] = 'Bu yükleme oturumunun süresi doldu.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function completedResponse(int $userId, array $data): JsonResponse
    {
        $video = $this->completedVideo($userId, $data);
        $video->load(['user', 'category'])->loadCount(['likes', 'comments']);

        return (new VideoResource($video))
            ->additional(['message' => 'Bu yükleme daha önce tamamlandı.'])
            ->response();
    }

    private function completedVideo(int $userId, array $data): Video
    {
        $video = Video::query()
            ->where('user_id', $userId)
            ->where('video_path', $data['video_key'])
            ->first();

        if (! $video || $video->thumbnail !== ($data['thumbnail_key'] ?? null)) {
            throw ValidationException::withMessages([
                'video_key' => 'Tamamlanmış yükleme oturumunun video kaydıyla eşleşmiyor.',
            ]);
        }

        return $video;
    }
}

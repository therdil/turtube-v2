<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VideoResource;
use App\Jobs\ProcessUploadedVideo;
use App\Models\UploadBatch;
use App\Models\UploadSession;
use App\Models\Video;
use App\Services\ContentCache;
use App\Services\R2UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'kind' => ['nullable', 'string', Rule::in(['video', 'thumbnail'])],
            'batch_id' => ['nullable', 'uuid'],
        ]);

        if (! $this->matchesContentType($data['extension'], $data['content_type'])) {
            throw ValidationException::withMessages([
                'content_type' => 'Dosya uzantısı ve içerik türü birbiriyle eşleşmelidir.',
            ]);
        }

        $strictMode = filled($data['kind'] ?? null) || filled($data['batch_id'] ?? null);
        $batch = null;

        if ($strictMode) {
            if (! filled($data['kind'] ?? null)) {
                throw ValidationException::withMessages(['kind' => 'Batch yüklemesi için medya türü gereklidir.']);
            }

            $this->ensureKindMatchesContentType($data['kind'], $data['content_type']);

            if ($data['kind'] === 'video') {
                if (filled($data['batch_id'] ?? null)) {
                    throw ValidationException::withMessages(['batch_id' => 'Yeni video yüklemesi yeni bir batch oluşturmalıdır.']);
                }

                $batch = UploadBatch::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $request->user('sanctum')->id,
                    'status' => UploadBatch::STATUS_PENDING,
                    'expires_at' => now()->addSeconds(R2UploadService::UPLOAD_EXPIRATION_SECONDS),
                ]);
            } else {
                if (! filled($data['batch_id'] ?? null)) {
                    throw ValidationException::withMessages(['batch_id' => 'Thumbnail yüklemesi için batch_id gereklidir.']);
                }

                $batch = $this->batchForOwner($request->user('sanctum')->id, $data['batch_id']);
                $this->ensureBatchCanComplete($batch);

                if ($batch->sessions()->where('kind', 'thumbnail')->exists()) {
                    throw ValidationException::withMessages(['batch_id' => 'Bu batch için zaten bir thumbnail yüklemesi başlatıldı.']);
                }
            }
        }

        $upload = $uploads->createUpload($data['extension'], $data['content_type']);
        try {
            $session = UploadSession::query()->create([
                'user_id' => $request->user('sanctum')->id,
                'batch_id' => $batch?->id,
                'object_key' => $upload['key'],
                'content_type' => $data['content_type'],
                'extension' => $data['extension'],
                'kind' => $strictMode ? $data['kind'] : null,
                'status' => UploadSession::STATUS_PENDING,
                'expires_at' => now()->addSeconds($upload['expires_in']),
            ]);
        } catch (QueryException $exception) {
            if ($batch && $strictMode) {
                throw ValidationException::withMessages([
                    'batch_id' => 'Bu batch için bu medya türünde bir yükleme zaten başlatıldı.',
                ]);
            }

            throw $exception;
        }

        // Optional for newer clients; the established Android fields remain unchanged.
        $upload['session_id'] = $session->id;
        if ($batch) {
            $upload['batch_id'] = $batch->uuid;
        }

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
            'batch_id' => ['nullable', 'uuid'],
        ]);

        $user = $request->user('sanctum');
        $batch = filled($data['batch_id'] ?? null) ? $this->batchForOwner($user->id, $data['batch_id']) : null;
        if ($batch && $batch->status !== UploadBatch::STATUS_COMPLETED) {
            $this->ensureBatchCanComplete($batch);
        }
        $sessions = $this->sessionsFor($user->id, $data, false, $batch);

        if ($batch?->status === UploadBatch::STATUS_COMPLETED || $sessions['video']->status === UploadSession::STATUS_COMPLETED) {
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

        $result = DB::transaction(function () use ($data, $user, $batch): array {
            $lockedBatch = $batch ? $this->batchForOwner($user->id, $batch->uuid, true) : null;
            $sessions = $this->sessionsFor($user->id, $data, true, $lockedBatch);

            if ($lockedBatch?->status === UploadBatch::STATUS_COMPLETED || $sessions['video']->status === UploadSession::STATUS_COMPLETED) {
                return ['video' => $this->completedVideo($user->id, $data), 'created' => false];
            }

            if ($lockedBatch) {
                $this->ensureBatchCanComplete($lockedBatch);
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

            if ($lockedBatch) {
                $lockedBatch->update([
                    'status' => UploadBatch::STATUS_COMPLETED,
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

    private function ensureKindMatchesContentType(string $kind, string $contentType): void
    {
        $matches = ($kind === 'video' && $contentType === 'video/mp4')
            || ($kind === 'thumbnail' && str_starts_with($contentType, 'image/'));

        if (! $matches) {
            throw ValidationException::withMessages([
                'kind' => 'Medya türü ile içerik türü birbiriyle eşleşmelidir.',
            ]);
        }
    }

    /** @return array{video: UploadSession, thumbnail?: UploadSession} */
    private function sessionsFor(int $userId, array $data, bool $lock = false, ?UploadBatch $batch = null): array
    {
        $sessions = [];

        foreach (['video' => 'video_key', 'thumbnail' => 'thumbnail_key'] as $type => $key) {
            if (! filled($data[$key] ?? null)) {
                continue;
            }

            $query = UploadSession::query()->forOwnerAndKey($userId, $data[$key]);
            if ($batch) {
                $query->where('batch_id', $batch->id)->where('kind', $type);
            }
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

    private function batchForOwner(int $userId, string $uuid, bool $lock = false): UploadBatch
    {
        $query = UploadBatch::query()->where('user_id', $userId)->where('uuid', $uuid);
        if ($lock) {
            $query->lockForUpdate();
        }

        $batch = $query->first();
        if (! $batch) {
            throw ValidationException::withMessages(['batch_id' => 'Bu yükleme batch\'ine erişim izniniz yok veya batch bulunamadı.']);
        }

        return $batch;
    }

    private function ensureBatchCanComplete(UploadBatch $batch): void
    {
        if ($batch->status !== UploadBatch::STATUS_PENDING) {
            throw ValidationException::withMessages(['batch_id' => 'Bu yükleme batch\'i artık tamamlanamaz.']);
        }

        if ($batch->expires_at->addSeconds(120)->isPast()) {
            $batch->update(['status' => UploadBatch::STATUS_EXPIRED]);
            throw ValidationException::withMessages(['batch_id' => 'Bu yükleme batch\'inin süresi doldu.']);
        }
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

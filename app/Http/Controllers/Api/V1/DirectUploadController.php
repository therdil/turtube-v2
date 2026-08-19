<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VideoResource;
use App\Jobs\ProcessUploadedVideo;
use App\Models\Video;
use App\Services\ContentCache;
use App\Services\R2UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DirectUploadController extends Controller
{
    public function initiate(Request $request, R2UploadService $uploads): JsonResponse
    {
        $data = $request->validate([
            'extension' => ['required', 'string', Rule::in(['mp4', 'jpg', 'jpeg', 'png', 'webp'])],
            'content_type' => ['required', 'string', Rule::in([
                'video/mp4',
                'image/jpeg',
                'image/png',
                'image/webp',
            ])],
        ]);

        $upload = $uploads->createUpload(
            $data['extension'],
            $data['content_type'],
        );

        return response()->json([
            'upload' => $upload,
        ], 201);
    }

    public function complete(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $data = $request->validate([
            'video_key' => [
                'required',
                'string',
                'regex:/^uploads\/[0-9a-fA-F-]{36}\.mp4$/',
            ],
            'thumbnail_key' => [
                'nullable',
                'string',
                'regex:/^uploads\/[0-9a-fA-F-]{36}\.(jpg|jpeg|png|webp)$/i',
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'status' => ['required', 'in:public,private,unlisted,draft'],
            'license' => ['nullable', 'in:standard,creative_commons'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:50', 'distinct'],
            'is_short' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
        ]);

        $disk = Storage::disk(config('video.disk'));

        if (! $disk->exists($data['video_key'])) {
            return response()->json([
                'message' => 'Video dosyası R2 üzerinde bulunamadı.',
            ], 422);
        }

        if (! empty($data['thumbnail_key']) && ! $disk->exists($data['thumbnail_key'])) {
            return response()->json([
                'message' => 'Thumbnail dosyası R2 üzerinde bulunamadı.',
            ], 422);
        }

        $isShort = (bool) ($data['is_short'] ?? false);

        $video = DB::transaction(function () use ($data, $user, $isShort): Video {
            return Video::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'video_path' => $data['video_key'],
                'thumbnail' => $data['thumbnail_key'] ?? null,
                'processing_status' => 'pending',
                'channel_name' => $user->name,
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'status' => $data['status'],
                'license' => $data['license'] ?? ($user->default_video_license ?: 'standard'),
                'tags' => collect($data['tags'] ?? [])
                    ->map(fn ($tag) => trim((string) $tag))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
                'is_short' => $isShort,
                'is_premium' => (bool) ($data['is_premium'] ?? false),
                'views' => 0,
                'duration' => 0,
            ]);
        });

        ProcessUploadedVideo::dispatch($video);
        ContentCache::flush();

        $video->load(['user', 'category'])->loadCount(['likes', 'comments']);

        return (new VideoResource($video))
            ->additional([
                'message' => $isShort
                    ? 'Shorts yüklemesi alındı; medya işleniyor.'
                    : 'Video yüklemesi alındı; medya işleniyor.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}

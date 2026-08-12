<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMobileVideoRequest;
use App\Http\Resources\Api\VideoResource;
use App\Jobs\ProcessUploadedVideo;
use App\Models\Video;
use App\Services\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function storeVideo(StoreMobileVideoRequest $request): JsonResponse
    {
        return $this->store($request, false);
    }

    public function storeShort(StoreMobileVideoRequest $request): JsonResponse
    {
        return $this->store($request, true);
    }

    private function store(StoreMobileVideoRequest $request, bool $isShort): JsonResponse
    {
        $this->authorize('create', Video::class);
        $disk = Storage::disk(config('video.disk'));
        $videoPath = $request->file('video')->store('videos', config('video.disk'));
        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('thumbnails', config('video.disk'))
            : null;

        try {
            $video = DB::transaction(function () use ($request, $videoPath, $thumbnailPath, $isShort): Video {
                return Video::query()->create([
                    'title' => $request->string('title')->toString(),
                    'description' => $request->input('description'),
                    'video_path' => $videoPath,
                    'thumbnail' => $thumbnailPath,
                    'processing_status' => 'pending',
                    'channel_name' => $request->user('sanctum')->name,
                    'user_id' => $request->user('sanctum')->id,
                    'category_id' => $request->integer('category_id'),
                    'status' => $request->string('status')->toString(),
                    'license' => $request->input('license', $request->user('sanctum')->default_video_license ?: 'standard'),
                    'tags' => collect($request->input('tags', []))
                        ->map(fn ($tag) => trim((string) $tag))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                    'is_short' => $isShort,
                    'is_premium' => $request->boolean('is_premium'),
                    'views' => 0,
                    'duration' => 0,
                ]);
            });
        } catch (\Throwable $exception) {
            $disk->delete(array_filter([$videoPath, $thumbnailPath]));

            throw $exception;
        }

        // The database transaction has completed before dispatching, matching
        // the existing web upload flow and avoiding a worker seeing no record.
        ProcessUploadedVideo::dispatch($video);
        ContentCache::flush();

        $video->load(['user', 'category'])->loadCount(['likes', 'comments']);

        return (new VideoResource($video))
            ->additional([
                'message' => $isShort ? 'Shorts yüklemesi alındı; medya işleniyor.' : 'Video yüklemesi alındı; medya işleniyor.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}

<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\VideoProcessingService;
use App\Services\ContentCache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class ProcessUploadedVideo implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;
    public int $timeout = 3600;

    public function __construct(public Video $video)
    {
        $this->onQueue('media');
    }

    public function handle(VideoProcessingService $videoProcessing): void
    {
        $video = Video::query()->findOrFail($this->video->id);

        $video->update([
            'processing_status' => 'processing',
            'processing_error' => null,
        ]);

        $processedMedia = [
            'preview' => $videoProcessing->generatePreview($video->video_path),
            'video_qualities' => $videoProcessing->generateQualities($video->video_path),
            'duration' => $videoProcessing->getDuration($video->video_path),
            'processing_status' => 'ready',
        ];

        if (! $video->thumbnail) {
            $processedMedia['thumbnail'] = $videoProcessing->generateThumbnail($video->video_path);
        }

        $video->update($processedMedia);

        ContentCache::flush();
    }

    public function failed(Throwable $exception): void
    {
        Video::query()
            ->whereKey($this->video->id)
            ->update([
                'processing_status' => 'failed',
                'processing_error' => Str::limit($exception->getMessage(), 1000),
                'updated_at' => now(),
            ]);

        ContentCache::flush();
    }
}

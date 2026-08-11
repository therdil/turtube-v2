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
use Illuminate\Support\Facades\Log;
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

        $processedMedia = ['processing_status' => 'ready'];

        if (! $videoProcessing->isAvailable()) {
            $video->update($processedMedia);
            ContentCache::flush();

            return;
        }

        if (! $video->thumbnail) {
            try {
                $processedMedia['thumbnail'] = $videoProcessing->generateThumbnail(
                    $video->video_path,
                    $video->is_short,
                );
            } catch (Throwable $exception) {
                Log::warning('Video thumbnail oluşturulamadı.', [
                    'video_id' => $video->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        try {
            $processedMedia['preview'] = $videoProcessing->generatePreview($video->video_path);
        } catch (Throwable $exception) {
            Log::warning('Video önizlemesi oluşturulamadı.', [
                'video_id' => $video->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        try {
            $processedMedia['video_qualities'] = $videoProcessing->generateQualities($video->video_path);
        } catch (Throwable $exception) {
            Log::warning('Video kalite sürümleri oluşturulamadı.', [
                'video_id' => $video->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        try {
            $processedMedia['duration'] = $videoProcessing->getDuration($video->video_path);
        } catch (Throwable $exception) {
            Log::warning('Video süresi okunamadı.', [
                'video_id' => $video->id,
                'exception' => $exception->getMessage(),
            ]);
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

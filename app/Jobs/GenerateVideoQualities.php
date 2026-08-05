<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\VideoProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateVideoQualities implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;
    public int $timeout = 3600;

    public function __construct(public Video $video, public bool $force = false)
    {
        $this->onQueue('media');
    }

    public function handle(VideoProcessingService $videoProcessing): void
    {
        $video = Video::query()->findOrFail($this->video->id);

        if (! $this->force && ! empty($video->video_qualities)) {
            return;
        }

        if ($this->force) {
            foreach (collect($video->video_qualities ?? [])->pluck('path')->all() as $path) {
                if ($path !== $video->video_path && Storage::disk(config('video.disk'))->exists($path)) {
                    Storage::disk(config('video.disk'))->delete($path);
                }
            }
        }

        $video->update([
            'video_qualities' => $videoProcessing->generateQualities($video->video_path),
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;

class VideoDeletionService
{
    /** Delete files stored for a video before removing its database record. */
    public function delete(Video $video): void
    {
        $qualityPaths = collect($video->video_qualities ?? [])->pluck('path')->all();
        $paths = array_unique(array_filter([
            $video->thumbnail,
            $video->preview,
            $video->video_path,
            ...$qualityPaths,
        ]));

        foreach ($paths as $path) {
            if (Storage::disk(config('video.disk'))->exists($path)) {
                Storage::disk(config('video.disk'))->delete($path);
            }
        }

        foreach ($video->captions()->pluck('path') as $path) {
            if (Storage::disk(config('video.disk'))->exists($path)) {
                Storage::disk(config('video.disk'))->delete($path);
            }
        }

        $video->delete();
    }
}

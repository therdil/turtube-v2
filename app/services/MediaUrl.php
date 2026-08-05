<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    public static function for(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $cdnUrl = rtrim((string) config('video.cdn_url'), '/');

        return $cdnUrl !== ''
            ? $cdnUrl.'/'.ltrim($path, '/')
            : Storage::disk(config('video.disk'))->url($path);
    }
}

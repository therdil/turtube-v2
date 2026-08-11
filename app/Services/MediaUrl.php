<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * Return a URL that native mobile clients can play or load directly.
     */
    public static function absoluteFor(?string $path): ?string
    {
        $url = static::for($path);

        if (! $url || Str::startsWith($url, ['https://', 'http://'])) {
            return $url;
        }

        return url($url);
    }
}

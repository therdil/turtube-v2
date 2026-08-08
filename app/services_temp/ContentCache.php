<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class ContentCache
{
    public static function remember(string $scope, string $suffix, int $seconds, Closure $callback): mixed
    {
        $version = (int) Cache::get('content-cache.version', 1);

        return Cache::remember(
            "content-cache:v{$version}:{$scope}:{$suffix}",
            now()->addSeconds($seconds),
            $callback,
        );
    }

    /** Invalidate every public discovery cache after content changes. */
    public static function flush(): void
    {
        Cache::forever('content-cache.version', ((int) Cache::get('content-cache.version', 1)) + 1);
    }
}

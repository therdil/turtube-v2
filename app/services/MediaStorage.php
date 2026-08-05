<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorage
{
    public static function diskName(): string
    {
        return (string) config('video.disk', 'public');
    }

    public static function disk()
    {
        return Storage::disk(self::diskName());
    }

    public static function withLocalPath(string $path, Closure $callback): mixed
    {
        $disk = self::disk();

        if (config('filesystems.disks.'.self::diskName().'.driver') === 'local') {
            return $callback($disk->path($path));
        }

        $directory = storage_path('app/media-work');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $temporaryPath = $directory.'/'.Str::uuid().'-'.basename($path);
        $source = $disk->readStream($path);
        if (! is_resource($source)) {
            throw new \RuntimeException('Uzak medya dosyasına erişilemedi: '.$path);
        }
        $target = fopen($temporaryPath, 'wb');
        stream_copy_to_stream($source, $target);
        fclose($target);
        if (is_resource($source)) {
            fclose($source);
        }

        try {
            return $callback($temporaryPath);
        } finally {
            @unlink($temporaryPath);
        }
    }

    public static function temporaryPath(string $extension): string
    {
        $directory = storage_path('app/media-work');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory.'/'.Str::uuid().'.'.$extension;
    }

    public static function putFile(string $path, string $localPath): void
    {
        $stream = fopen($localPath, 'rb');

        try {
            self::disk()->put($path, $stream, ['visibility' => 'public']);
        } finally {
            fclose($stream);
            @unlink($localPath);
        }
    }
}

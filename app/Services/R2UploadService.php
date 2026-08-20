<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2UploadService
{
    public const UPLOAD_EXPIRATION_SECONDS = 900;

    /**
     * Create a short-lived, direct-to-R2 PUT capability for one media object.
     *
     * R2 credentials remain on the Laravel server. Native clients receive only
     * the resulting short-lived presigned URL.
     *
     * @return array{key: string, upload_url: string, expires_in: int, media_url: string}
     */
    public function createUpload(string $extension, string $contentType): array
    {
        $key = $this->keyFor($extension, $contentType);
        $disk = config('filesystems.disks.r2');

        $command = $this->client()->getCommand('PutObject', [
            'Bucket' => $disk['bucket'],
            'Key' => $key,
            'ContentType' => $contentType,
        ]);

        $request = $this->client()->createPresignedRequest(
            $command,
            '+'.self::UPLOAD_EXPIRATION_SECONDS.' seconds',
        );

        return [
            'key' => $key,
            'upload_url' => (string) $request->getUri(),
            'expires_in' => self::UPLOAD_EXPIRATION_SECONDS,
            'media_url' => MediaUrl::absoluteFor($key),
        ];
    }

    public function exists(string $key): bool
    {
        return Storage::disk('r2')->exists($key);
    }

    private function keyFor(string $extension, string $contentType): string
    {
        $directory = str_starts_with($contentType, 'image/') ? 'thumbnails' : 'videos';

        return $directory.'/'.Str::uuid().'.'.strtolower($extension);
    }

    private function client(): S3Client
    {
        $disk = config('filesystems.disks.r2');

        return new S3Client([
            'version' => 'latest',
            'region' => $disk['region'],
            'endpoint' => $disk['endpoint'],
            'use_path_style_endpoint' => (bool) $disk['use_path_style_endpoint'],
            'credentials' => [
                'key' => $disk['key'],
                'secret' => $disk['secret'],
            ],
        ]);
    }
}

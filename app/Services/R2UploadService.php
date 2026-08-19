<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class R2UploadService
{
    private S3Client $client;

    public function __construct()
    {
        $disk = config('filesystems.disks.r2');

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $disk['region'] ?? 'auto',
            'endpoint' => $disk['endpoint'],
            'credentials' => [
                'key' => $disk['key'],
                'secret' => $disk['secret'],
            ],
            'use_path_style_endpoint' => $disk['use_path_style_endpoint'] ?? true,
        ]);
    }

    public function createUpload(
        string $extension,
        string $contentType,
        string $directory = 'uploads',
    ): array {
        $extension = strtolower(ltrim($extension, '.'));
        $key = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        $command = $this->client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.r2.bucket'),
            'Key' => $key,
            'ContentType' => $contentType,
        ]);

        $request = $this->client->createPresignedRequest(
            $command,
            '+15 minutes',
        );

        return [
            'key' => $key,
            'upload_url' => (string) $request->getUri(),
            'expires_in' => 900,
            'media_url' => rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.$key,
        ];
    }

    public function exists(string $key): bool
    {
        return Storage::disk(config('video.disk'))->exists($key);
    }
}

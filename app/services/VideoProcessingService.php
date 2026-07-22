<?php

namespace App\Services;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoProcessingService
{
    protected FFMpeg $ffmpeg;
    protected FFProbe $ffprobe;

    public function __construct()
    {
        $ffmpeg = 'D:\\Tools\\FFmpeg\\bin\\ffmpeg.exe';
        $ffprobe = 'D:\\Tools\\FFmpeg\\bin\\ffprobe.exe';

        $config = [
            'ffmpeg.binaries'  => $ffmpeg,
            'ffprobe.binaries' => $ffprobe,
            'timeout'          => 3600,
        ];

        $this->ffmpeg = FFMpeg::create($config);
        $this->ffprobe = FFProbe::create($config);
    }

    /**
     * Thumbnail oluşturur.
     */
    public function generateThumbnail(string $videoPath): string
    {
        $absoluteVideo = Storage::disk('public')->path($videoPath);

        if (!Storage::disk('public')->exists('thumbnails')) {
            Storage::disk('public')->makeDirectory('thumbnails');
        }

        $thumbnailRelative = 'thumbnails/' . Str::uuid() . '.jpg';
        $thumbnailAbsolute = Storage::disk('public')->path($thumbnailRelative);

        $this->ffmpeg
            ->open($absoluteVideo)
            ->frame(TimeCode::fromSeconds(1))
            ->save($thumbnailAbsolute);

        return $thumbnailRelative;
    }

    /**
     * Hover için kısa preview videosu oluşturur.
     */
    public function generatePreview(string $videoPath): string
    {
        $absoluteVideo = Storage::disk('public')->path($videoPath);

        if (!Storage::disk('public')->exists('previews')) {
            Storage::disk('public')->makeDirectory('previews');
        }

        $previewRelative = 'previews/' . Str::uuid() . '.mp4';
        $previewAbsolute = Storage::disk('public')->path($previewRelative);

        $duration = max(1, $this->getDuration($videoPath));

        $start = 1;
        $length = min(6, max(1, $duration - $start));

        $ffmpeg = 'D:\\Tools\\FFmpeg\\bin\\ffmpeg.exe';

        $command = sprintf(
            '"%s" -y -ss %d -i "%s" -t %d -vf "scale=854:-2" -c:v libx264 -preset veryfast -crf 30 -c:a aac -b:a 128k "%s"',
            $ffmpeg,
            $start,
            $absoluteVideo,
            $length,
            $previewAbsolute
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            throw new \RuntimeException(
                "Preview oluşturulamadı.\n\n" . implode("\n", $output)
            );
        }

        return $previewRelative;
    }

    /**
     * Video süresini saniye olarak döndürür.
     */
    public function getDuration(string $videoPath): int
    {
        $absoluteVideo = Storage::disk('public')->path($videoPath);

        return (int) $this->ffprobe
            ->format($absoluteVideo)
            ->get('duration');
    }
}
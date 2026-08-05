<?php

namespace App\Services;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Support\Str;

class VideoProcessingService
{
    protected FFMpeg $ffmpeg;
    protected FFProbe $ffprobe;

    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('video.ffmpeg_binary'),
            'ffprobe.binaries' => config('video.ffprobe_binary'),
            'timeout' => 3600,
        ]);
        $this->ffprobe = FFProbe::create([
            'ffmpeg.binaries' => config('video.ffmpeg_binary'),
            'ffprobe.binaries' => config('video.ffprobe_binary'),
            'timeout' => 3600,
        ]);
    }

    public function generateThumbnail(string $videoPath): string
    {
        return MediaStorage::withLocalPath($videoPath, function (string $source) {
            $temporary = MediaStorage::temporaryPath('jpg');
            $relative = 'thumbnails/'.Str::uuid().'.jpg';

            $this->ffmpeg->open($source)->frame(TimeCode::fromSeconds(1))->save($temporary);
            MediaStorage::putFile($relative, $temporary);

            return $relative;
        });
    }

    public function generatePreview(string $videoPath): string
    {
        return MediaStorage::withLocalPath($videoPath, function (string $source) use ($videoPath) {
            $temporary = MediaStorage::temporaryPath('mp4');
            $relative = 'previews/'.Str::uuid().'.mp4';
            $duration = max(1, $this->getDuration($videoPath));
            $length = min(6, max(1, $duration - 1));

            $this->run(sprintf(
                '"%s" -y -ss 1 -i "%s" -t %d -vf "scale=854:-2" -c:v libx264 -preset veryfast -crf 30 -c:a aac -b:a 128k -movflags +faststart "%s"',
                config('video.ffmpeg_binary'),
                $source,
                $length,
                $temporary,
            ));

            MediaStorage::putFile($relative, $temporary);

            return $relative;
        });
    }

    /** @return array<int, array{label: string, path: string}> */
    public function generateQualities(string $videoPath): array
    {
        $sourceHeight = $this->getVideoHeight($videoPath);
        $qualities = [[
            'label' => $sourceHeight ? $sourceHeight.'p' : 'Orijinal',
            'path' => $videoPath,
        ]];

        if ($sourceHeight <= 0) {
            return $qualities;
        }

        return MediaStorage::withLocalPath($videoPath, function (string $source) use ($sourceHeight, $qualities) {
            foreach ([1080 => '5M', 720 => '2800k', 480 => '1400k'] as $height => $bitrate) {
                if ($height >= $sourceHeight) {
                    continue;
                }

                $temporary = MediaStorage::temporaryPath('mp4');
                $relative = 'qualities/'.Str::uuid().'-'.$height.'p.mp4';
                $result = $this->run(sprintf(
                    '"%s" -y -i "%s" -vf "scale=-2:%d" -c:v libx264 -preset veryfast -b:v %s -maxrate %s -bufsize %s -c:a aac -b:a 128k -movflags +faststart "%s"',
                    config('video.ffmpeg_binary'), $source, $height, $bitrate, $bitrate, $bitrate, $temporary,
                ), false);

                if ($result === 0 && is_file($temporary)) {
                    MediaStorage::putFile($relative, $temporary);
                    $qualities[] = ['label' => $height.'p', 'path' => $relative];
                } else {
                    @unlink($temporary);
                }
            }

            return $qualities;
        });
    }

    public function getDuration(string $videoPath): int
    {
        return MediaStorage::withLocalPath($videoPath, fn (string $source) => (int) $this->ffprobe->format($source)->get('duration'));
    }

    private function getVideoHeight(string $videoPath): int
    {
        return MediaStorage::withLocalPath($videoPath, fn (string $source) => (int) $this->ffprobe->streams($source)->videos()->first()->get('height'));
    }

    private function run(string $command, bool $throwOnFailure = true): int
    {
        exec($command, $output, $result);

        if ($throwOnFailure && $result !== 0) {
            throw new \RuntimeException('FFmpeg medya dosyası üretemedi: '.implode("\n", $output));
        }

        return $result;
    }
}

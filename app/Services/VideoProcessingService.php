<?php

namespace App\Services;

use FFMpeg\FFProbe;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class VideoProcessingService
{
    protected ?FFProbe $ffprobe = null;
    protected ?bool $available = null;

    public function isAvailable(): bool
    {
        return $this->available ??= $this->binaryIsAvailable(config('video.ffmpeg_binary'))
            && $this->binaryIsAvailable(config('video.ffprobe_binary'));
    }

    public function generateThumbnail(string $videoPath, bool $isShort = false): string
    {
        $this->ensureAvailable();

        return MediaStorage::withLocalPath($videoPath, function (string $source) use ($isShort) {
            $temporary = MediaStorage::temporaryPath('jpg');
            $relative = 'thumbnails/'.Str::uuid().'.jpg';

            $duration = (float) $this->ffprobe()->format($source)->get('duration');
            $captureAt = $duration > 0
                ? min(max(0.5, $duration * 0.1), max(0.5, $duration - 0.25))
                : 1.0;
            $filter = $isShort
                ? 'scale=720:1280:force_original_aspect_ratio=increase,crop=720:1280'
                : 'scale=1280:720:force_original_aspect_ratio=increase,crop=1280:720';
            $process = new Process([
                config('video.ffmpeg_binary'),
                '-y',
                '-ss',
                (string) $captureAt,
                '-i',
                $source,
                '-frames:v',
                '1',
                '-vf',
                $filter,
                '-q:v',
                '3',
                $temporary,
            ]);
            $process->setTimeout(30);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($temporary)) {
                @unlink($temporary);

                throw new \RuntimeException('Video thumbnail oluşturulamadı.');
            }

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
        $this->ensureAvailable();

        return MediaStorage::withLocalPath($videoPath, fn (string $source) => (int) $this->ffprobe()->format($source)->get('duration'));
    }

    private function getVideoHeight(string $videoPath): int
    {
        $this->ensureAvailable();

        return MediaStorage::withLocalPath($videoPath, fn (string $source) => (int) $this->ffprobe()->streams($source)->videos()->first()->get('height'));
    }

    private function ensureAvailable(): void
    {
        if (! $this->isAvailable()) {
            throw new \RuntimeException('FFmpeg veya FFprobe kullanılamıyor.');
        }
    }

    private function ffprobe(): FFProbe
    {
        $this->ensureAvailable();

        return $this->ffprobe ??= FFProbe::create([
            'ffmpeg.binaries' => config('video.ffmpeg_binary'),
            'ffprobe.binaries' => config('video.ffprobe_binary'),
            'timeout' => 3600,
        ]);
    }

    private function binaryIsAvailable(string $binary): bool
    {
        try {
            $process = new Process([$binary, '-version']);
            $process->setTimeout(5);
            $process->run();

            return $process->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
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

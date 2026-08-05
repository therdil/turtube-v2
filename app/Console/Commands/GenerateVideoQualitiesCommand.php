<?php

namespace App\Console\Commands;

use App\Jobs\GenerateVideoQualities;
use App\Models\Video;
use Illuminate\Console\Command;

class GenerateVideoQualitiesCommand extends Command
{
    protected $signature = 'videos:generate-qualities {--force : Mevcut kalite varyantlarını yeniden üret}';

    protected $description = 'Mevcut videolar için oynatma kalite varyantlarını kuyruğa ekler';

    public function handle(): int
    {
        $videos = Video::query()
            ->when(! $this->option('force'), fn ($query) => $query->whereNull('video_qualities'))
            ->whereNotNull('video_path')
            ->get();

        foreach ($videos as $video) {
            GenerateVideoQualities::dispatch($video, (bool) $this->option('force'));
        }

        $this->info($videos->count().' video için kalite üretimi kuyruğa eklendi.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Jobs;

use App\Models\VideoCaption;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessVideoCaption implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public VideoCaption $caption)
    {
        $this->onQueue('media');
    }

    public function handle(): void
    {
        $caption = VideoCaption::query()->findOrFail($this->caption->id);
        $content = Storage::disk(config('video.disk'))->get($caption->path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        if (! str_starts_with(ltrim($content), 'WEBVTT')) {
            $content = "WEBVTT\n\n".ltrim($content);
        }

        Storage::disk(config('video.disk'))->put($caption->path, $content, ['visibility' => 'public']);
    }
}

<?php

namespace App\View\Components\Watch;

use App\Models\Video;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WatchLaterButton extends Component
{
    public Video $video;

    public bool $isWatchLater;

    /**
     * Create a new component instance.
     */
    public function __construct(Video $video, bool $isWatchLater)
    {
        $this->video = $video;
        $this->isWatchLater = $isWatchLater;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.watch.watch-later-button');
    }
}
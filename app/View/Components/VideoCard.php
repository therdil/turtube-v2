<?php

namespace App\View\Components;

use App\Models\Video;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VideoCard extends Component
{
    public function __construct(
        public Video $video
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.video-card');
    }
}
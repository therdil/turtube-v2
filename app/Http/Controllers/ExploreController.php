<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function trending(): View
    {
        $videos = $this->trendingQuery()->paginate(16);

        return view('explore-trending', compact('videos'));
    }

    public function channels(): View
    {
        $channels = $this->channelsQuery()->paginate(24);

        return view('explore-channels', compact('channels'));
    }

    private function trendingQuery()
    {
        return Video::query()->published()->with(['user', 'category'])->orderByDesc('views')->latest();
    }

    private function channelsQuery()
    {
        return User::query()
            ->withCount(['videos' => fn ($query) => $query->published(), 'subscribers'])
            ->withSum(['videos' => fn ($query) => $query->published()], 'views')
            ->orderByDesc('subscribers_count')
            ->orderByDesc('videos_count')
            ->orderBy('name');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Services\ContentCache;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function trending(): View
    {
        $page = max(1, (int) request('page', 1));
        $videos = auth()->guest()
            ? ContentCache::remember('trending', 'page:'.$page, 180, fn () => $this->trendingQuery()->paginate(16))
            : $this->trendingQuery()->paginate(16);

        return view('explore-trending', compact('videos'));
    }

    public function channels(): View
    {
        $page = max(1, (int) request('page', 1));
        $channels = auth()->guest()
            ? ContentCache::remember('channels', 'page:'.$page, 300, fn () => $this->channelsQuery()->paginate(24))
            : $this->channelsQuery()->paginate(24);

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

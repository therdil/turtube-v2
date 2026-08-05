<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\User;
use App\Services\ContentCache;
use App\Services\AnalyticsService;

class HomeController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    public function index()
    {
        $viewer = auth()->user();
        $videoQuery = fn () => $this->query();

        $videos = $videoQuery()
            ->when($viewer, fn ($query) => $query->with([
                'progress' => fn ($progress) => $progress->where('user_id', $viewer->id),
            ]))
            ->paginate(16);

        $this->analyticsService->recordImpressions($videos->getCollection());

        $watchHistory = $viewer
            ? $viewer->watchHistory()
                ->with(['video.user', 'video.category'])
                ->whereHas('video', fn ($query) => $query->published())
                ->take(8)
                ->get()
                ->pluck('video')
                ->filter()
            : collect();
        $watchedIds = $watchHistory->pluck('id')->all();
        $watchedCategories = $watchHistory->pluck('category_id')->filter()->unique()->all();

        $forYou = $videoQuery()
            ->when($watchedIds, fn ($query) => $query->whereNotIn('id', $watchedIds))
            ->when($watchedCategories, fn ($query) => $query->whereIn('category_id', $watchedCategories))
            ->orderByDesc('views')
            ->take(8)
            ->get();
        $trendingVideos = $videoQuery()->orderByDesc('views')->take(8)->get();
        $shorts = $videoQuery()->shorts()->orderByDesc('views')->take(8)->get();
        $premiumVideos = $videoQuery()->where('is_premium', true)->orderByDesc('views')->take(8)->get();
        $popularChannels = User::query()
            ->whereHas('videos', fn ($query) => $query->published())
            ->withCount([
                'subscribers',
                'videos as public_videos_count' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('subscribers_count')
            ->take(6)
            ->get();

        return view('home', compact(
            'videos', 'watchHistory', 'forYou', 'trendingVideos', 'shorts', 'premiumVideos', 'popularChannels'
        ));
    }

    private function query()
    {
        return Video::query()
            ->published()
            ->with(['user', 'category'])
            ->orderByDesc('is_featured')
            ->latest();
    }
}

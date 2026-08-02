<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $videosQuery = Video::where('user_id', $user->id);

        $stats = [

            'videos' => $videosQuery->count(),

            'views' => $videosQuery->sum('views'),

            'comments' => Comment::whereHas('video', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),

            'likes' => Video::where('user_id', $user->id)
                ->withCount('likes')
                ->get()
                ->sum('likes_count'),

        ];

        $latestVideos = Video::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $topVideos = Video::where('user_id', $user->id)
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $recentViews = Video::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->pluck('views');

        return view('studio.dashboard', [
            'stats' => $stats,
            'latestVideos' => $latestVideos,
            'topVideos' => $topVideos,
            'recentViews' => $recentViews,
        ]);
    }
}
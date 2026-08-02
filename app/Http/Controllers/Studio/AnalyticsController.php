<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Video;
use App\Models\VideoAnalytics;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $videos = Video::where('user_id', $user->id);

        $stats = [

            'videos' => $videos->count(),

            'views' => $videos->sum('views'),

            'likes' => Video::where('user_id', $user->id)
                ->withCount('likes')
                ->get()
                ->sum('likes_count'),

            'comments' => Comment::whereHas('video', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),

        ];

        $topVideos = Video::where('user_id', $user->id)
            ->orderByDesc('views')
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Son 30 Gün Analytics
        |--------------------------------------------------------------------------
        */

        $analytics = VideoAnalytics::query()
            ->whereHas('video', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereDate('date', '>=', now()->subDays(29))
            ->orderBy('date')
            ->get();

        $chartLabels = $analytics
            ->pluck('date')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('d M'))
            ->values();

        $chartViews = $analytics
            ->pluck('views')
            ->values();

        return view('studio.analytics.index', [

            'stats' => $stats,

            'topVideos' => $topVideos,

            'chartLabels' => $chartLabels,

            'chartViews' => $chartViews,

        ]);
    }
}
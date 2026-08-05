<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoAnalytics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:7,30,365'],
        ]);

        $days = (int) ($validated['period'] ?? 30);
        $from = now()->subDays($days - 1)->startOfDay();

        $daily = VideoAnalytics::query()
            ->whereDate('date', '>=', $from)
            ->selectRaw('date, SUM(views) as views, SUM(watch_time) as watch_time, SUM(likes) as likes, SUM(comments) as comments')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn (VideoAnalytics $analytics) => $analytics->date->toDateString());

        $dates = collect(range($days - 1, 0))->map(fn (int $daysAgo) => now()->subDays($daysAgo));
        $chart = $dates->map(fn ($date) => [
            'label' => $date->format($days > 30 ? 'd M' : 'd M'),
            'views' => (int) data_get($daily->get($date->toDateString()), 'views', 0),
        ]);

        $stats = [
            'views' => (int) $daily->sum('views'),
            'watchTime' => (int) $daily->sum('watch_time'),
            'likes' => (int) $daily->sum('likes'),
            'comments' => (int) $daily->sum('comments'),
        ];

        $topVideos = Video::query()
            ->with('user')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        return view('admin.analytics', compact('days', 'chart', 'stats', 'topVideos'));
    }
}

<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\VideoAnalytics;
use App\Models\VideoViewEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:7,28,30,365'],
            'group' => ['nullable', 'in:day,week,month'],
        ]);

        $user = Auth::user();
        $daysInPeriod = (int) ($validated['period'] ?? 30);
        $group = $validated['group'] ?? ($daysInPeriod === 365 ? 'month' : 'day');
        $periodStart = now()->subDays($daysInPeriod - 1)->startOfDay();

        $periodAnalytics = VideoAnalytics::query()
            ->whereHas('video', fn ($query) => $query->where('user_id', $user->id))
            ->with('video:id,duration')
            ->whereDate('date', '>=', $periodStart)
            ->get();
        $daily = $periodAnalytics
            ->groupBy(fn (VideoAnalytics $analytics) => $analytics->date->toDateString())
            ->map(fn ($items) => [
                'views' => (int) $items->sum('views'),
                'watch_time' => (int) $items->sum('watch_time'),
                'impressions' => (int) $items->sum('impressions'),
            ]);

        $chart = $this->chartPoints($daily, $daysInPeriod, $group);

        $periodViews = (int) $periodAnalytics->sum('views');
        $watchTime = (int) $periodAnalytics->sum('watch_time');
        $watchPotential = $periodAnalytics->sum(fn (VideoAnalytics $item) => $item->views * (int) ($item->video?->duration ?? 0));
        $impressions = (int) $periodAnalytics->sum('impressions');

        $stats = [
            'videos' => Video::where('user_id', $user->id)->count(),
            'views' => $periodViews,
            'likes' => Video::where('user_id', $user->id)->withCount('likes')->get()->sum('likes_count'),
            'comments' => Comment::whereHas('video', fn ($query) => $query->where('user_id', $user->id))->count(),
            'watchTime' => $watchTime,
            'averageWatchTime' => $periodViews > 0 ? (int) round($watchTime / $periodViews) : 0,
            'viewPercentage' => $watchPotential > 0 ? round(min(100, ($watchTime / $watchPotential) * 100), 1) : 0,
            'ctr' => $impressions > 0 ? round(min(100, ($periodViews / $impressions) * 100), 1) : null,
        ];

        $events = VideoViewEvent::query()
            ->whereHas('video', fn ($query) => $query->where('user_id', $user->id))
            ->where('viewed_at', '>=', $periodStart);

        $subscriberBase = Subscription::query()->where('channel_id', $user->id);
        $subscriberChange = (clone $subscriberBase)
            ->where('created_at', '>=', $periodStart)
            ->count();
        $previousSubscriberChange = (clone $subscriberBase)
            ->whereBetween('created_at', [$periodStart->copy()->subDays($daysInPeriod), $periodStart])
            ->count();
        $realtimeViews = VideoViewEvent::query()
            ->whereHas('video', fn ($query) => $query->where('user_id', $user->id))
            ->where('viewed_at', '>=', now()->subMinutes(60))
            ->count();

        $trafficSources = $this->breakdown((clone $events), 'source');
        $devices = $this->breakdown((clone $events), 'device');
        $countries = $this->breakdown((clone $events), 'country', 10);

        $topVideos = Video::where('user_id', $user->id)->orderByDesc('views')->take(10)->get();

        return view('studio.analytics.index', compact(
            'stats', 'topVideos', 'chart', 'daysInPeriod', 'group', 'trafficSources', 'devices', 'countries',
            'subscriberChange', 'previousSubscriberChange', 'realtimeViews'
        ));
    }

    private function breakdown($query, string $column, int $limit = 6)
    {
        return $query
            ->selectRaw($column.' as label, COUNT(*) as views')
            ->groupBy($column)
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    private function chartPoints($daily, int $daysInPeriod, string $group)
    {
        $start = now()->subDays($daysInPeriod - 1)->startOfDay();
        $end = now()->endOfDay();

        if ($group === 'day') {
            return collect(range($daysInPeriod - 1, 0))->map(function (int $daysAgo) use ($daily) {
                $date = now()->subDays($daysAgo);
                return ['label' => $date->format('d M'), 'views' => (int) data_get($daily->get($date->toDateString()), 'views', 0), 'watch_time' => (int) data_get($daily->get($date->toDateString()), 'watch_time', 0)];
            });
        }

        $cursor = $group === 'week' ? $start->copy()->startOfWeek() : $start->copy()->startOfMonth();
        $points = collect();

        while ($cursor->lte($end)) {
            $pointEnd = $group === 'week' ? $cursor->copy()->endOfWeek() : $cursor->copy()->endOfMonth();
            $values = $daily->filter(fn ($value, $date) => $date >= $cursor->toDateString() && $date <= $pointEnd->toDateString());
            $points->push([
                'label' => $group === 'week' ? $cursor->format('d M') : $cursor->translatedFormat('M Y'),
                'views' => (int) $values->sum('views'),
                'watch_time' => (int) $values->sum('watch_time'),
            ]);
            $cursor = $group === 'week' ? $cursor->addWeek()->startOfWeek() : $cursor->addMonth()->startOfMonth();
        }

        return $points;
    }
}

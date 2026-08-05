<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\VideoViewEvent;
use App\Models\VideoAnalytics;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('studio.dashboard', $this->dashboardData($request->user()));
    }

    public function summary(Request $request): JsonResponse
    {
        $data = $this->dashboardData($request->user());

        return response()->json([
            'periods' => $data['periods'],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function dashboardData(User $user): array
    {
        $videos = Video::query()->where('user_id', $user->id);
        $eventBase = VideoViewEvent::query()->whereHas('video', fn ($query) => $query->where('user_id', $user->id));
        $now = now();

        $periods = [
            'live' => (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subMinutes(60))->count(),
            '24h' => (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subHours(24))->count(),
            '48h' => (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subHours(48))->count(),
            '7d' => (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subDays(7))->count(),
            '28d' => (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subDays(28))->count(),
            '30d' => (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subDays(30))->count(),
        ];

        $analytics28 = VideoAnalytics::query()
            ->whereHas('video', fn ($query) => $query->where('user_id', $user->id))
            ->with('video:id,duration')
            ->whereDate('date', '>=', $now->copy()->subDays(27)->startOfDay())
            ->get();
        $dailyAnalytics = $analytics28
            ->groupBy(fn (VideoAnalytics $analytics) => $analytics->date->toDateString())
            ->map(fn ($items) => [
                'views' => (int) $items->sum('views'),
                'watch_time' => (int) $items->sum('watch_time'),
            ]);
        $dailyChart = collect(range(13, 0))->map(function (int $daysAgo) use ($dailyAnalytics) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->format('d M'),
                'views' => (int) data_get($dailyAnalytics->get($date->toDateString()), 'views', 0),
            ];
        });
        $watchTime = (int) $analytics28->sum('watch_time');
        $analyticsViews = (int) $analytics28->sum('views');
        $watchPotential = $analytics28->sum(fn (VideoAnalytics $item) => $item->views * (int) ($item->video?->duration ?? 0));
        $impressions = (int) $analytics28->sum('impressions');

        $stats = [
            'videos' => (clone $videos)->count(),
            'views' => (clone $videos)->sum('views'),
            'comments' => Comment::query()->whereHas('video', fn ($query) => $query->where('user_id', $user->id))->count(),
            'likes' => (clone $videos)->withCount('likes')->get()->sum('likes_count'),
            'watchTime' => $watchTime,
            'averageWatchTime' => $analyticsViews > 0 ? (int) round($watchTime / $analyticsViews) : 0,
            'viewPercentage' => $watchPotential > 0 ? round(min(100, ($watchTime / $watchPotential) * 100), 1) : 0,
            'ctr' => $impressions > 0 ? round(min(100, ($analyticsViews / $impressions) * 100), 1) : null,
        ];

        $latestVideos = (clone $videos)->with('category')->latest()->take(5)->get();
        $topVideos = (clone $videos)->orderByDesc('views')->take(5)->get();
        $recentComments = Comment::query()
            ->whereHas('video', fn ($query) => $query->where('user_id', $user->id))
            ->with(['user', 'video'])
            ->latest()
            ->take(5)
            ->get();
        $recentSubscribers = Subscription::query()
            ->where('channel_id', $user->id)
            ->with('subscriber')
            ->latest()
            ->take(5)
            ->get();
        $eventPeriod = (clone $eventBase)->where('viewed_at', '>=', $now->copy()->subDays(28));
        $trafficSources = (clone $eventPeriod)
            ->selectRaw('source as label, COUNT(*) as views')
            ->groupBy('source')
            ->orderByDesc('views')
            ->limit(5)
            ->get();
        $countries = (clone $eventPeriod)
            ->selectRaw('country as label, COUNT(*) as views')
            ->groupBy('country')
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        $lastWeek = (clone $eventBase)
            ->where('viewed_at', '>=', $now->copy()->subDays(7))
            ->selectRaw('video_id, COUNT(*) as views')
            ->groupBy('video_id')
            ->pluck('views', 'video_id');
        $previousWeek = (clone $eventBase)
            ->whereBetween('viewed_at', [$now->copy()->subDays(14), $now->copy()->subDays(7)])
            ->selectRaw('video_id, COUNT(*) as views')
            ->groupBy('video_id')
            ->pluck('views', 'video_id');
        $fastestVideoId = $lastWeek->keys()
            ->sortByDesc(fn ($videoId) => (int) $lastWeek[$videoId] - (int) ($previousWeek[$videoId] ?? 0))
            ->first();
        $fastestGrowingVideo = $fastestVideoId ? Video::query()->where('user_id', $user->id)->find($fastestVideoId) : null;
        $fastestGrowth = $fastestVideoId ? (int) $lastWeek[$fastestVideoId] - (int) ($previousWeek[$fastestVideoId] ?? 0) : 0;

        $suggestions = collect();
        if ($stats['videos'] === 0) $suggestions->push('İlk videonu yükleyerek kanalını keşfete açabilirsin.');
        if ($periods['7d'] === 0 && $stats['videos'] > 0) $suggestions->push('Son 7 gündeki görüntülenme düşük. Yeni bir başlık veya thumbnail denemeyi düşün.');
        if ((clone $videos)->whereNull('thumbnail')->exists()) $suggestions->push('Thumbnail’i olmayan videoların tıklanma ihtimali düşebilir.');
        if ($recentComments->isNotEmpty()) $suggestions->push('Son yorumlara yanıt vermek topluluk etkileşimini güçlendirir.');
        if ($suggestions->isEmpty()) $suggestions->push('Kanalın düzenli büyüyor. En hızlı büyüyen videonun konusunu yeni bir içerikte derinleştirebilirsin.');

        return compact('stats', 'periods', 'dailyChart', 'trafficSources', 'countries', 'latestVideos', 'topVideos', 'recentComments', 'recentSubscribers', 'fastestGrowingVideo', 'fastestGrowth', 'suggestions');
    }
}

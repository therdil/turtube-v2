<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VideoResource;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\VideoAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudioAnalyticsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['period' => ['nullable', 'in:7,28,30,365']]);
        $user = $request->user('sanctum');
        $days = (int) ($data['period'] ?? 30);
        $start = now()->subDays($days - 1)->startOfDay();
        $videos = Video::query()->where('user_id', $user->id);
        $analytics = VideoAnalytics::query()->whereHas('video', fn ($query) => $query->where('user_id', $user->id))
            ->with('video:id,duration')->whereDate('date', '>=', $start)->get();
        $views = (int) $analytics->sum('views');
        $watchTime = (int) $analytics->sum('watch_time');
        $impressions = (int) $analytics->sum('impressions');
        $potential = $analytics->sum(fn (VideoAnalytics $item) => $item->views * (int) ($item->video?->duration ?? 0));
        $dailyValues = $analytics->groupBy(fn (VideoAnalytics $item) => $item->date->toDateString());
        $daily = collect(range($days - 1, 0))->map(fn (int $ago) => $date = now()->subDays($ago)->toDateString())
            ->map(fn (string $date) => ['date' => $date, 'views' => (int) ($dailyValues->get($date)?->sum('views') ?? 0), 'watch_time' => (int) ($dailyValues->get($date)?->sum('watch_time') ?? 0)]);
        $topVideos = (clone $videos)->with(['user', 'category'])->withCount(['likes', 'comments'])->orderByDesc('views')->take(10)->get();

        return response()->json(['data' => [
            'period' => $days,
            'stats' => [
                'views' => $views, 'videos' => (clone $videos)->count(),
                'shorts' => (clone $videos)->where('is_short', true)->count(),
                'likes' => (clone $videos)->withCount('likes')->get()->sum('likes_count'),
                'comments' => Comment::query()->whereHas('video', fn ($query) => $query->where('user_id', $user->id))->count(),
                'subscribers' => Subscription::where('channel_id', $user->id)->count(),
                'watch_time' => $watchTime,
                'average_watch_time' => $views > 0 ? (int) round($watchTime / $views) : 0,
                'view_percentage' => $potential > 0 ? round(min(100, $watchTime / $potential * 100), 1) : 0,
                'ctr' => $impressions > 0 ? round(min(100, $views / $impressions * 100), 1) : null,
            ],
            'daily' => $daily->values(),
            'top_videos' => VideoResource::collection($topVideos),
        ]]);
    }
}

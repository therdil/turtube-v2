<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoReport;
use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;

/**
 * Read-only server-authoritative metrics for the Android administrator dashboard.
 */
class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'users' => User::query()->count(),
                'videos' => Video::query()->where('is_short', false)->count(),
                'shorts' => Video::query()->where('is_short', true)->count(),
                'views' => (int) Video::query()->sum('views'),
                'comments' => Comment::query()->count(),
                'active_users' => WatchHistory::query()
                    ->where('watched_at', '>=', now()->subDays(30))
                    ->distinct('user_id')
                    ->count('user_id'),
                'open_reports' => VideoReport::query()
                    ->whereIn('status', ['open', 'reviewing'])
                    ->count(),
            ],
        ]);
    }
}

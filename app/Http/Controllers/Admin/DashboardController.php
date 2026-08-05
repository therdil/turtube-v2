<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\LiveStream;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoAnalytics;
use App\Models\VideoReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $storage = $this->storageUsage();
        $queue = $this->queueStatus();
        $daily = VideoAnalytics::query()
            ->whereDate('date', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('date, SUM(views) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn (VideoAnalytics $analytics) => $analytics->date->toDateString());
        $dailyChart = collect(range(13, 0))->map(function (int $daysAgo) use ($daily) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->format('d M'),
                'views' => (int) data_get($daily->get($date->toDateString()), 'views', 0),
            ];
        });

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'videos' => Video::count(),
                'liveStreams' => LiveStream::where('status', 'live')->count(),
                'premiumUsers' => User::where('premium_until', '>', now())->count(),
                'openReports' => VideoReport::whereIn('status', ['open', 'reviewing'])->count(),
            ],
            'latestUsers' => User::latest()->take(6)->get(),
            'latestActivities' => Schema::hasTable('admin_activities')
                ? AdminActivity::query()->with('admin:id,name')->latest()->take(8)->get()
                : collect(),
            'dailyChart' => $dailyChart,
            'storage' => $storage,
            'queue' => $queue,
            'system' => [
                'environment' => app()->environment(),
                'php_version' => PHP_VERSION,
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
            ],
        ]);
    }

    private function storageUsage(): array
    {
        return Cache::remember('admin-dashboard-storage-usage', 300, function (): array {
            try {
                $disk = Storage::disk(config('video.disk'));
                $bytes = collect(['videos', 'thumbnails', 'previews', 'qualities', 'captions'])
                    ->flatMap(fn (string $directory) => $disk->allFiles($directory))
                    ->unique()
                    ->sum(fn (string $path) => $disk->size($path));

                return ['bytes' => $bytes, 'status' => 'available'];
            } catch (\Throwable) {
                return ['bytes' => 0, 'status' => 'unavailable'];
            }
        });
    }

    private function queueStatus(): array
    {
        $queueDriver = config('queue.default');
        $jobsTable = config('queue.connections.database.table', 'jobs');

        return [
            'driver' => $queueDriver,
            'pending' => $queueDriver === 'database' && Schema::hasTable($jobsTable) ? DB::table($jobsTable)->count() : null,
            'failed' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
        ];
    }
}

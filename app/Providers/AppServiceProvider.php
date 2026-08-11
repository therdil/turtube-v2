<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\LiveStream;
use App\Models\Playlist;
use App\Models\SiteSetting;
use App\Models\Video;
use App\Policies\CommentPolicy;
use App\Policies\LiveStreamPolicy;
use App\Policies\PlaylistPolicy;
use App\Policies\VideoPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Video::class, VideoPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Playlist::class, PlaylistPolicy::class);
        Gate::policy(LiveStream::class, LiveStreamPolicy::class);

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        RateLimiter::for('registration', fn (Request $request) => Limit::perHour(5)
            ->by($request->ip()));

        RateLimiter::for('video-upload', fn (Request $request) => Limit::perHour(10)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        RateLimiter::for('comments', fn (Request $request) => Limit::perMinute(15)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        RateLimiter::for('interactions', fn (Request $request) => Limit::perMinute(60)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        RateLimiter::for('reports', fn (Request $request) => Limit::perHour(5)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        RateLimiter::for('live', fn (Request $request) => Limit::perHour(10)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(30)
            ->by($request->ip()));

        View::composer('*', function ($view) {
            $categories = Cache::remember('navigation-categories-v4', now()->addHour(), fn () =>
                Category::query()->orderBy('name')->get()
            );

            if (! $categories instanceof Collection || $categories->contains(fn ($category) => ! $category instanceof Category)) {
                Cache::forget('navigation-categories-v4');
                $categories = Category::query()->orderBy('name')->get();
                Cache::put('navigation-categories-v4', $categories, now()->addHour());
            }

            $view->with('categories', $categories);

        });

        View::composer('layouts.turtube', function ($view) {
            $view->with('siteSettings', SiteSetting::current());
        });
    }
}

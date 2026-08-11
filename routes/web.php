<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LiveStreamController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionFeedController;
use App\Http\Controllers\ShortController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoProgressController;
use App\Http\Controllers\VideoCaptionController;
use App\Http\Controllers\VideoChapterController;
use App\Http\Controllers\VideoReportController;
use App\Http\Controllers\VideoFavoriteController;
use App\Http\Controllers\VideoRatingController;
use App\Http\Controllers\WatchHistoryController;
use App\Http\Controllers\WatchLaterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/search', [SearchController::class, 'index'])
    ->middleware('throttle:search')
    ->name('search');

Route::get('/search/suggestions', [SearchController::class, 'suggestions'])
    ->middleware('throttle:search')
    ->name('search.suggestions');

Route::get('/trending', [ExploreController::class, 'trending'])
    ->name('trending');

Route::get('/channels', [ExploreController::class, 'channels'])
    ->name('channels.index');

Route::get('/shorts', [ShortController::class, 'index'])
    ->name('shorts.index');

Route::get('/shorts/{video}', [ShortController::class, 'show'])
    ->name('shorts.show');

Route::get('/live', [LiveStreamController::class, 'index'])
    ->name('live.index');

Route::get('/live/create', [LiveStreamController::class, 'create'])
    ->middleware('auth')
    ->name('live.create');

Route::get('/live/{stream}', [LiveStreamController::class, 'show'])
    ->name('live.show');

Route::get('/premium', [PremiumController::class, 'index'])
    ->name('premium.index');

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::get('/category/{category:slug}', [CategoryController::class, 'show'])
    ->name('categories.show');

Route::get('/videos/{video}', [VideoController::class, 'show'])
    ->name('videos.show');

Route::post('/videos/{video}/age-confirmation', [VideoController::class, 'confirmAge'])
    ->middleware('throttle:interactions')
    ->name('videos.age-confirmation');

Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])
    ->name('playlists.show');

Route::get('/@{user:name}', [ChannelController::class, 'show'])
    ->name('channels.show');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Video
    |--------------------------------------------------------------------------
    */

    Route::get('/upload', [VideoController::class, 'create'])
        ->name('videos.create');

    Route::post('/upload', [VideoController::class, 'store'])
        ->middleware('throttle:video-upload')
        ->name('videos.store');

    Route::get('/my-videos', [VideoController::class, 'myVideos'])
        ->name('videos.mine');

    Route::get('/videos/{video}/edit', [VideoController::class, 'edit'])
        ->name('videos.edit');

    Route::put('/videos/{video}', [VideoController::class, 'update'])
        ->name('videos.update');

    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])
        ->name('videos.destroy');

    Route::post('/videos/{video}/chapters', [VideoChapterController::class, 'store'])
        ->middleware('throttle:video-upload')
        ->name('videos.chapters.store');

    Route::delete('/videos/{video}/chapters/{chapter}', [VideoChapterController::class, 'destroy'])
        ->name('videos.chapters.destroy');

    Route::post('/videos/{video}/captions', [VideoCaptionController::class, 'store'])
        ->middleware('throttle:video-upload')
        ->name('videos.captions.store');

    Route::delete('/videos/{video}/captions/{caption}', [VideoCaptionController::class, 'destroy'])
        ->name('videos.captions.destroy');

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:comments')
        ->name('comments.store');

    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::patch('/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('throttle:comments')
        ->name('comments.update');

    Route::post('/comments/{comment}/reaction', [CommentController::class, 'toggleReaction'])
        ->middleware('throttle:interactions')
        ->name('comments.reaction');

    Route::patch('/videos/{video}/comments/{comment}/pin', [CommentController::class, 'togglePin'])
        ->middleware('throttle:interactions')
        ->name('comments.pin');

    Route::post('/videos/{video}/reports', [VideoReportController::class, 'store'])
        ->middleware('throttle:reports')
        ->name('videos.reports.store');

    /*
    |--------------------------------------------------------------------------
    | Likes
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/like', [LikeController::class, 'toggle'])
        ->middleware('throttle:interactions')
        ->name('videos.like');

    Route::post('/videos/{video}/dislike', [LikeController::class, 'toggleDislike'])
        ->middleware('throttle:interactions')
        ->name('videos.dislike');

    Route::post('/videos/{video}/favorite', [VideoFavoriteController::class, 'toggle'])
        ->middleware('throttle:interactions')
        ->name('videos.favorite');

    Route::get('/favorites', [VideoFavoriteController::class, 'index'])
        ->name('favorites.index');

    Route::post('/videos/{video}/rating', [VideoRatingController::class, 'store'])
        ->middleware('throttle:interactions')
        ->name('videos.rating');

    Route::get('/liked-videos', [LikeController::class, 'index'])
        ->name('liked-videos.index');

    /*
    |--------------------------------------------------------------------------
    | Watch Later
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/watch-later', [WatchLaterController::class, 'toggle'])
        ->middleware('throttle:interactions')
        ->name('watch-later.toggle');

    Route::get('/watch-later', [WatchLaterController::class, 'index'])
        ->name('watch-later.index');

    Route::get('/subscriptions', [SubscriptionFeedController::class, 'index'])
        ->name('subscriptions.index');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::delete('/notifications', [NotificationController::class, 'clear'])
        ->middleware('throttle:interactions')
        ->name('notifications.clear');

    Route::get('/notifications/{notification}', [NotificationController::class, 'readAndVisit'])
        ->name('notifications.visit');
    
    /*
    |--------------------------------------------------------------------------
    | Watch History
    |--------------------------------------------------------------------------
    */

    Route::get('/history', [WatchHistoryController::class, 'index'])
        ->name('history.index');

    Route::delete('/history', [WatchHistoryController::class, 'destroy'])
        ->name('history.destroy');    
    
    /*
    |--------------------------------------------------------------------------
    | Video Progress
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/progress', [VideoProgressController::class, 'store'])
        ->middleware('throttle:interactions')
        ->name('videos.progress');    

    /*
    |--------------------------------------------------------------------------
    | Playlist
    |--------------------------------------------------------------------------
    */

    Route::get('/playlists', [PlaylistController::class, 'index'])
        ->name('playlists.index');

    Route::get('/playlists/create', [PlaylistController::class, 'create'])
        ->name('playlists.create');

    Route::post('/playlists', [PlaylistController::class, 'store'])
        ->name('playlists.store');

    Route::post('/playlists/{playlist}/toggle', [PlaylistController::class, 'toggle'])
        ->middleware('throttle:interactions')
        ->name('playlists.toggle');

    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])
        ->name('playlists.destroy');

    /*
    |--------------------------------------------------------------------------
    | Subscription
    |--------------------------------------------------------------------------
    */

    Route::post('/channels/{channel}/subscribe', [SubscriptionController::class, 'toggle'])
        ->middleware('throttle:interactions')
        ->name('channels.subscribe');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])
        ->name('profile.theme');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::post('/live', [LiveStreamController::class, 'store'])
        ->middleware('throttle:live')
        ->name('live.store');

    Route::post('/live/{stream}/start', [LiveStreamController::class, 'start'])
        ->middleware('throttle:live')
        ->name('live.start');

    Route::post('/live/{stream}/stop', [LiveStreamController::class, 'stop'])
        ->middleware('throttle:live')
        ->name('live.stop');

});

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Creator Studio
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('studio')
    ->name('studio.')
    ->group(function () {

        Route::get('/', \App\Http\Controllers\Studio\DashboardController::class)
            ->name('dashboard');

        Route::get('/dashboard/summary', [\App\Http\Controllers\Studio\DashboardController::class, 'summary'])
            ->middleware('throttle:interactions')
            ->name('dashboard.summary');

        Route::get('/videos', [\App\Http\Controllers\Studio\VideoController::class, 'index'])
            ->name('videos.index');

        Route::get('/videos/export', [\App\Http\Controllers\Studio\VideoController::class, 'export'])
            ->name('videos.export');

        Route::patch('/videos/bulk', [\App\Http\Controllers\Studio\VideoController::class, 'bulkUpdate'])
            ->name('videos.bulk-update');

        Route::get('/comments', [\App\Http\Controllers\Studio\CommentController::class, 'index'])
            ->name('comments.index');

        Route::delete('/comments/{comment}', [\App\Http\Controllers\Studio\CommentController::class, 'destroy'])
            ->name('comments.destroy');

        Route::get('/analytics', [\App\Http\Controllers\Studio\AnalyticsController::class, 'index'])
            ->name('analytics.index');

        Route::get('/channel', [\App\Http\Controllers\Studio\ChannelController::class, 'index'])
            ->name('channel.index');

        Route::put('/channel', [\App\Http\Controllers\Studio\ChannelController::class, 'update'])
            ->name('channel.update');

    });

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)
            ->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])
            ->name('users.index');
        Route::post('/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])
            ->name('users.toggle-admin');
        Route::patch('/users/{user}/premium', [\App\Http\Controllers\Admin\UserController::class, 'updatePremium'])
            ->name('users.premium');
        Route::patch('/users/{user}/verified', [\App\Http\Controllers\Admin\UserController::class, 'toggleVerified'])
            ->name('users.verified');
        Route::patch('/users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'toggleBan'])
            ->name('users.ban');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])
            ->name('users.show');
        Route::get('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'index'])
            ->name('videos.index');
        Route::patch('/videos/{video}/status', [\App\Http\Controllers\Admin\VideoController::class, 'updateStatus'])
            ->name('videos.status');
        Route::patch('/videos/{video}/moderation', [\App\Http\Controllers\Admin\VideoController::class, 'updateModeration'])
            ->name('videos.moderation');
        Route::patch('/videos/bulk', [\App\Http\Controllers\Admin\VideoController::class, 'bulkUpdate'])
            ->name('videos.bulk-update');
        Route::get('/reports', [\App\Http\Controllers\Admin\VideoReportController::class, 'index'])
            ->name('reports.index');
        Route::patch('/reports/{report}', [\App\Http\Controllers\Admin\VideoReportController::class, 'update'])
            ->name('reports.update');
        Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])
            ->name('categories.index');
        Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])
            ->name('categories.store');
        Route::put('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])
            ->name('categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])
            ->name('categories.destroy');
        Route::get('/comments', [\App\Http\Controllers\Admin\CommentController::class, 'index'])
            ->name('comments.index');
        Route::delete('/comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])
            ->name('comments.destroy');
        Route::get('/live-streams', [\App\Http\Controllers\Admin\LiveStreamController::class, 'index'])
            ->name('live.index');
        Route::patch('/live-streams/{stream}/end', [\App\Http\Controllers\Admin\LiveStreamController::class, 'end'])
            ->name('live.end');
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])
            ->name('analytics.index');
        Route::get('/settings', [\App\Http\Controllers\Admin\SiteSettingController::class, 'index'])
            ->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SiteSettingController::class, 'update'])
            ->name('settings.update');
    });

require __DIR__.'/auth.php';

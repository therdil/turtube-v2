<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionFeedController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoProgressController;
use App\Http\Controllers\WatchHistoryController;
use App\Http\Controllers\WatchLaterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/search', [SearchController::class, 'index'])
    ->name('search');

Route::get('/trending', [ExploreController::class, 'trending'])
    ->name('trending');

Route::get('/channels', [ExploreController::class, 'channels'])
    ->name('channels.index');

Route::get('/category/{category:slug}', [CategoryController::class, 'show'])
    ->name('categories.show');

Route::get('/videos/{video}', [VideoController::class, 'show'])
    ->name('videos.show');

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
        ->name('videos.store');

    Route::get('/my-videos', [VideoController::class, 'myVideos'])
        ->name('videos.mine');

    Route::get('/videos/{video}/edit', [VideoController::class, 'edit'])
        ->name('videos.edit');

    Route::put('/videos/{video}', [VideoController::class, 'update'])
        ->name('videos.update');

    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])
        ->name('videos.destroy');

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Likes
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/like', [LikeController::class, 'toggle'])
        ->name('videos.like');

    Route::get('/liked-videos', [LikeController::class, 'index'])
        ->name('liked-videos.index');

    /*
    |--------------------------------------------------------------------------
    | Watch Later
    |--------------------------------------------------------------------------
    */

    Route::post('/videos/{video}/watch-later', [WatchLaterController::class, 'toggle'])
        ->name('watch-later.toggle');

    Route::get('/watch-later', [WatchLaterController::class, 'index'])
        ->name('watch-later.index');

    Route::get('/subscriptions', [SubscriptionFeedController::class, 'index'])
        ->name('subscriptions.index');
    
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
        ->name('playlists.toggle');

    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])
        ->name('playlists.destroy');

    /*
    |--------------------------------------------------------------------------
    | Subscription
    |--------------------------------------------------------------------------
    */

    Route::post('/channels/{channel}/subscribe', [SubscriptionController::class, 'toggle'])
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

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

Route::get('/dashboard', function () {
    return view('dashboard');
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

        Route::get('/videos', [\App\Http\Controllers\Studio\VideoController::class, 'index'])
            ->name('videos.index');

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

require __DIR__.'/auth.php';

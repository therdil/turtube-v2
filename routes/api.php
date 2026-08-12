<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChannelController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PlaylistController;
use App\Http\Controllers\Api\V1\Moderation\VideoReportController as ModerationVideoReportController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ShortController;
use App\Http\Controllers\Api\V1\StudioAnalyticsController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function (): void {
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
    Route::get('/shorts', [ShortController::class, 'index'])->name('shorts.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/search', SearchController::class)->middleware('throttle:search')->name('search');
    Route::get('/channels/{user}', [ChannelController::class, 'show'])->name('channels.show');

    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset')->name('forgot-password');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:registration')->name('register');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/me', [AuthController::class, 'me'])->name('me');
        });
    });

    Route::middleware('auth:sanctum')->prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/read', [NotificationController::class, 'markAllRead'])->middleware('throttle:interactions')->name('read-all');
        Route::patch('/{notification}/read', [NotificationController::class, 'markRead'])->middleware('throttle:interactions')->name('read');
    });

    Route::middleware(['auth:sanctum', 'throttle:video-upload'])->group(function (): void {
        Route::post('/videos', [UploadController::class, 'storeVideo'])->name('videos.store');
        Route::post('/shorts', [UploadController::class, 'storeShort'])->name('shorts.store');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
        Route::post('/playlists', [PlaylistController::class, 'store'])->middleware('throttle:interactions')->name('playlists.store');
        Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');
        Route::patch('/playlists/{playlist}', [PlaylistController::class, 'update'])->middleware('throttle:interactions')->name('playlists.update');
        Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->middleware('throttle:interactions')->name('playlists.destroy');
        Route::post('/playlists/{playlist}/videos', [PlaylistController::class, 'addVideo'])->middleware('throttle:interactions')->name('playlists.videos.store');
        Route::delete('/playlists/{playlist}/videos/{video}', [PlaylistController::class, 'removeVideo'])->middleware('throttle:interactions')->name('playlists.videos.destroy');
        Route::get('/studio/analytics', StudioAnalyticsController::class)->name('studio.analytics');
    });

    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });

    Route::middleware(['auth:sanctum', 'moderator'])->prefix('moderation')->name('moderation.')->group(function (): void {
        Route::get('/reports', [ModerationVideoReportController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}', [ModerationVideoReportController::class, 'update'])->name('reports.update');
    });
});

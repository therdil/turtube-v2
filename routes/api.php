<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountPreferencesController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\SystemNotificationController as AdminSystemNotificationController;
use App\Http\Controllers\Api\V1\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Api\V1\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\ChannelController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PlaylistController;
use App\Http\Controllers\Api\V1\Moderation\VideoReportController as ModerationVideoReportController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ShortController;
use App\Http\Controllers\Api\V1\StudioAnalyticsController;
use App\Http\Controllers\Api\V1\StudioVideoController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\VideoReportController as VideoReportSubmissionController;
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
            Route::patch('/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:interactions')->name('password.update');
        });
    });

    Route::middleware('auth:sanctum')->prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/read', [NotificationController::class, 'markAllRead'])->middleware('throttle:interactions')->name('read-all');
        Route::patch('/{notification}/read', [NotificationController::class, 'markRead'])->middleware('throttle:interactions')->name('read');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/notification-preferences', [AccountPreferencesController::class, 'notifications'])->name('notification-preferences.show');
        Route::patch('/notification-preferences', [AccountPreferencesController::class, 'updateNotifications'])->middleware('throttle:interactions')->name('notification-preferences.update');
        Route::get('/privacy-settings', [AccountPreferencesController::class, 'privacy'])->name('privacy-settings.show');
        Route::patch('/privacy-settings', [AccountPreferencesController::class, 'updatePrivacy'])->middleware('throttle:interactions')->name('privacy-settings.update');
        Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:interactions')->name('feedback.store');
    });

    Route::middleware(['auth:sanctum', 'throttle:video-upload'])->group(function (): void {
        Route::post('/videos', [UploadController::class, 'storeVideo'])->name('videos.store');
        Route::post('/shorts', [UploadController::class, 'storeShort'])->name('shorts.store');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:interactions')->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'storeAvatar'])->middleware('throttle:interactions')->name('profile.avatar.store');
        Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->middleware('throttle:interactions')->name('profile.avatar.destroy');
        Route::post('/profile/banner', [ProfileController::class, 'storeBanner'])->middleware('throttle:interactions')->name('profile.banner.store');
        Route::delete('/profile/banner', [ProfileController::class, 'destroyBanner'])->middleware('throttle:interactions')->name('profile.banner.destroy');
        Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
        Route::post('/playlists', [PlaylistController::class, 'store'])->middleware('throttle:interactions')->name('playlists.store');
        Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');
        Route::patch('/playlists/{playlist}', [PlaylistController::class, 'update'])->middleware('throttle:interactions')->name('playlists.update');
        Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->middleware('throttle:interactions')->name('playlists.destroy');
        Route::post('/playlists/{playlist}/videos', [PlaylistController::class, 'addVideo'])->middleware('throttle:interactions')->name('playlists.videos.store');
        Route::delete('/playlists/{playlist}/videos/{video}', [PlaylistController::class, 'removeVideo'])->middleware('throttle:interactions')->name('playlists.videos.destroy');
        Route::get('/studio/analytics', StudioAnalyticsController::class)->name('studio.analytics');
        Route::get('/studio/videos', StudioVideoController::class)->name('studio.videos');
        Route::post('/videos/{video}/reports', [VideoReportSubmissionController::class, 'store'])
            ->middleware('throttle:interactions')
            ->name('videos.reports.store');
    });

    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/reports', [ModerationVideoReportController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}', [ModerationVideoReportController::class, 'update'])->name('reports.update');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/system-notifications', [AdminSystemNotificationController::class, 'store'])
            ->middleware('throttle:interactions')
            ->name('system-notifications.store');
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::patch('/feedback/{feedback}', [AdminFeedbackController::class, 'update'])->name('feedback.update');
        Route::get('/videos', [AdminContentController::class, 'videos'])->name('videos.index');
        Route::patch('/videos/{video}', [AdminContentController::class, 'updateVideo'])->name('videos.update');
        Route::delete('/videos/{video}', [AdminContentController::class, 'destroyVideo'])->name('videos.destroy');
        Route::get('/comments', [AdminContentController::class, 'comments'])->name('comments.index');
        Route::delete('/comments/{comment}', [AdminContentController::class, 'destroyComment'])->name('comments.destroy');
        Route::get('/channels', [AdminContentController::class, 'channels'])->name('channels.index');
        Route::get('/categories', [AdminContentController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [AdminContentController::class, 'storeCategory'])->name('categories.store');
        Route::patch('/categories/{category:id}', [AdminContentController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category:id}', [AdminContentController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/settings', [AdminContentController::class, 'settings'])->name('settings.index');
    });

    Route::middleware(['auth:sanctum', 'moderator'])->prefix('moderation')->name('moderation.')->group(function (): void {
        Route::get('/reports', [ModerationVideoReportController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}', [ModerationVideoReportController::class, 'update'])->name('reports.update');
    });
});

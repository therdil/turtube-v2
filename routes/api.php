<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChannelController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ShortController;
use App\Http\Controllers\Api\V1\VideoController;
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
});

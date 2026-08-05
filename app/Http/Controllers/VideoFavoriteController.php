<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoFavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $videos = $request->user()
            ->favoriteVideos()
            ->where(function ($query) use ($request) {
                $query->where('status', 'public')
                    ->orWhere('videos.user_id', $request->user()->id);
            })
            ->with(['user', 'category'])
            ->latest('video_favorites.created_at')
            ->get();

        return view('favorites.index', compact('videos'));
    }

    public function toggle(Request $request, Video $video): JsonResponse
    {
        abort_unless(
            $video->isVisibleTo($request->user()) && $video->isPremiumAccessibleTo($request->user()),
            404
        );

        $user = $request->user();
        $exists = $user->favoriteVideos()->whereKey($video->id)->exists();

        if ($exists) {
            $user->favoriteVideos()->detach($video->id);
        } else {
            $user->favoriteVideos()->attach($video->id);
        }

        return response()->json([
            'favorited' => ! $exists,
            'favorites_count' => $video->favorites()->count(),
        ]);
    }
}

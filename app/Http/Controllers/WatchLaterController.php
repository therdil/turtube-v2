<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class WatchLaterController extends Controller
{
    /**
     * Daha Sonra İzle sayfası
     */
    public function index()
    {
        $videos = auth()->user()
            ->watchLaterVideos()
            ->where(function ($query) {
                $query->where('status', 'public')
                    ->orWhere('videos.user_id', auth()->id());
            })
            ->with([
                'user',
                'category',
            ])
            ->latest('watch_laters.created_at')
            ->get();

        return view('watch-later.index', compact('videos'));
    }

    /**
     * Daha Sonra İzle ekle / kaldır
     */
    public function toggle(Video $video)
    {
        abort_unless(
            $video->isVisibleTo(auth()->user()) && $video->isPremiumAccessibleTo(auth()->user()),
            404
        );

        $user = auth()->user();

        $exists = $user->watchLaterVideos()
            ->where('video_id', $video->id)
            ->exists();

        if ($exists) {

            $user->watchLaterVideos()->detach($video->id);

            return response()->json([
                'saved' => false,
                'message' => 'Video Daha Sonra İzle listesinden kaldırıldı.',
            ]);
        }

        $user->watchLaterVideos()->attach($video->id);

        return response()->json([
            'saved' => true,
            'message' => 'Video Daha Sonra İzle listesine eklendi.',
        ]);
    }
}

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
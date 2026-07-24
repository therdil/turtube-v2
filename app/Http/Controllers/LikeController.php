<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Video beğenisini aç / kapat (toggle)
     */
    public function toggle(Request $request, Video $video)
    {
        $existingLike = $video->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            $liked = false;
        } else {
            $video->likes()->create([
                'user_id' => auth()->id(),
            ]);

            $liked = true;
        }

        $likesCount = $video->likes()->count();

        // AJAX isteği geldiyse JSON döndür
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        }

        // Normal form isteği için geri dönüş
        return redirect()
            ->route('videos.show', $video)
            ->with(
                'success',
                $liked ? 'Video beğenildi.' : 'Beğeni kaldırıldı.'
            );
    }
}
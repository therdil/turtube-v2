<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\AnalyticsService;
use App\Notifications\VideoLikedNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LikeController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * Kullanıcının beğendiği videolar
     */
    public function index(): View
    {
        $videos = auth()->user()
            ->likedVideos()
            ->with(['user', 'category'])
            ->latest('video_likes.created_at')
            ->get();

        return view('liked-videos.index', compact('videos'));
    }

    /**
     * Video beğenisini aç / kapat (toggle)
     */
    public function toggle(Request $request, Video $video)
    {
        abort_unless(
            $video->isVisibleTo(auth()->user()) && $video->isPremiumAccessibleTo(auth()->user()),
            404
        );

        $existingReaction = $video->reactions()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingReaction?->reaction === 'like') {
            $existingReaction->delete();

            $liked = false;
        } else {
            $video->reactions()->updateOrCreate(
                ['user_id' => auth()->id()],
                ['reaction' => 'like'],
            );

            $liked = true;
            if ($video->user_id !== auth()->id()) {
                $video->user->notify(new VideoLikedNotification($video, $request->user()));
            }
        }

        $likesCount = $video->likes()->count();

        $this->analyticsService->syncLikes($video);

        // AJAX isteği geldiyse JSON döndür
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount,
                'dislikes_count' => $video->dislikes()->count(),
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

    public function toggleDislike(Request $request, Video $video)
    {
        abort_unless(
            $video->isVisibleTo($request->user()) && $video->isPremiumAccessibleTo($request->user()),
            404
        );

        $reaction = $video->reactions()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($reaction?->reaction === 'dislike') {
            $reaction->delete();
            $disliked = false;
        } else {
            $video->reactions()->updateOrCreate(
                ['user_id' => $request->user()->id],
                ['reaction' => 'dislike'],
            );
            $disliked = true;
        }

        $this->analyticsService->syncLikes($video);

        return response()->json([
            'success' => true,
            'disliked' => $disliked,
            'likes_count' => $video->likes()->count(),
            'dislikes_count' => $video->dislikes()->count(),
        ]);
    }
}

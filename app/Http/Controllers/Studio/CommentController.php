<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    public function index()
    {
        $comments = Comment::query()
            ->with([
                'user',
                'video',
            ])
            ->whereHas('video', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(20);

        return view('studio.comments.index', compact('comments'));
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $video = $comment->video;

        $comment->delete();

        $this->analyticsService->syncComments($video);

        return back()->with('success', 'Yorum silindi.');
    }
}

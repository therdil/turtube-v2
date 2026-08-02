<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
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
        if ($comment->video->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Yorum silindi.');
    }
}
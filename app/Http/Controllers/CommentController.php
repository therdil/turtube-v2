<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Yeni yorum ekle
     */
    public function store(Request $request, Video $video)
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $video->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()
            ->route('videos.show', $video)
            ->with('success', 'Yorum başarıyla eklendi.');
    }

    /**
     * Yorumu sil
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $video = $comment->video;

        $comment->delete();

        return redirect()
            ->route('videos.show', $video)
            ->with('success', 'Yorum başarıyla silindi.');
    }
}
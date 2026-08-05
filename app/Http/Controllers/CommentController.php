<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use App\Services\AnalyticsService;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * Yeni yorum ekle
     */
    public function store(Request $request, Video $video)
    {
        abort_unless(
            $video->isVisibleTo($request->user()) && $video->isPremiumAccessibleTo($request->user()),
            404
        );

        $this->authorize('create', [Comment::class, $video]);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        if (! empty($validated['parent_id'])) {
            abort_unless(
                Comment::query()
                    ->whereKey($validated['parent_id'])
                    ->where('video_id', $video->id)
                    ->exists(),
                422,
                'Yalnızca bu videodaki bir yoruma yanıt verebilirsiniz.'
            );
        }

        $comment = $video->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $this->analyticsService->syncComments($video);

        if ($video->user_id !== auth()->id()) {
            $video->user->notify(new NewCommentNotification($comment));
        }

        return redirect()
            ->route('videos.show', $video)
            ->with('success', 'Yorum başarıyla eklendi.');
    }

    /**
     * Yorumu sil
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $video = $comment->video;

        $comment->delete();

        $this->analyticsService->syncComments($video);

        return redirect()
            ->route('videos.show', $video)
            ->with('success', 'Yorum başarıyla silindi.');
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $comment->update(['comment' => $validated['comment']]);

        return back()->with('success', 'Yorum güncellendi.');
    }

    public function togglePin(Video $video, Comment $comment)
    {
        abort_unless($comment->video_id === $video->id, 404);
        abort_if($comment->parent_id, 422, 'Yalnızca ana yorumlar sabitlenebilir.');
        $this->authorize('pin', $comment);

        if ($comment->is_pinned) {
            $comment->update(['is_pinned' => false]);

            return back()->with('success', 'Sabit yorum kaldırıldı.');
        }

        $video->comments()->update(['is_pinned' => false]);
        $comment->update(['is_pinned' => true]);

        return back()->with('success', 'Yorum videonun başına sabitlendi.');
    }
}

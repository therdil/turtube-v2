<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\AnalyticsService;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private AdminActivityLogger $activityLogger,
    )
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $comments = Comment::query()
            ->with(['user', 'video.user'])
            ->when(filled($validated['q'] ?? null), function ($query) use ($validated) {
                $query->where(function ($comments) use ($validated) {
                    $comments->where('comment', 'like', '%'.$validated['q'].'%')
                        ->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$validated['q'].'%'))
                        ->orWhereHas('video', fn ($videos) => $videos->where('title', 'like', '%'.$validated['q'].'%'));
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.comments', compact('comments'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $video = $comment->video;
        $comment->delete();
        $this->analyticsService->syncComments($video);
        $this->activityLogger->record(auth()->user(), 'comment.removed', 'Yorum moderasyon nedeniyle kaldirildi.', $comment, ['video_id' => $video?->id]);

        return back()->with('success', 'Yorum kaldırıldı.');
    }
}

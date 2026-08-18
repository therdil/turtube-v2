<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct(private readonly Comment $comment)
    {
        // The production worker already consumes the default queue.
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notification_comments_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'comment',
            'title' => 'Yeni yorum',
            'message' => $this->comment->user->name.' videonuza yorum yaptı.',
            'url' => route('videos.show', $this->comment->video),
            'video_id' => $this->comment->video_id,
            'comment_id' => $this->comment->id,
            'channel_id' => $this->comment->user_id,
        ];
    }
}

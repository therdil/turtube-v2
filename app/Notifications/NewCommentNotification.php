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
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'comment',
            'title' => 'Yeni yorum',
            'message' => $this->comment->user->name.' videonuza yorum yaptı.',
            'url' => route('videos.show', $this->comment->video),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VideoLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Video $video, private readonly User $actor)
    {
        // Keep notification jobs on the queue the existing worker consumes.
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notification_likes_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'like',
            'title' => 'Videon beğenildi',
            'message' => $this->actor->name.' videonu beğendi.',
            'url' => route('videos.show', $this->video),
            'video_id' => $this->video->id,
            'channel_id' => $this->actor->id,
        ];
    }
}

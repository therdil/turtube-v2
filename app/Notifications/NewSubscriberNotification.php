<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSubscriberNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct(private readonly User $subscriber)
    {
        // The production worker already consumes the default queue.
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notification_subscribers_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'subscriber',
            'title' => 'Yeni abone',
            'message' => $this->subscriber->name.' kanalınıza abone oldu.',
            'url' => route('channels.show', $this->subscriber),
            'channel_id' => $this->subscriber->id,
        ];
    }
}

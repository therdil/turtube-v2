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
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'subscriber',
            'title' => 'Yeni abone',
            'message' => $this->subscriber->name.' kanalınıza abone oldu.',
            'url' => route('channels.show', $this->subscriber),
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Reusable database notification for platform-originated messages.
 * Callers provide only safe navigation references; arbitrary models are never
 * serialized into the notification payload.
 */
class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<string, string|int|null> $payload */
    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly array $payload = [],
        private readonly bool $mandatory = false,
    ) {
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return ($this->mandatory || $notifiable->notification_system_enabled) ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'system',
            'title' => $this->title,
            'message' => $this->message,
            ...array_filter($this->payload, static fn ($value): bool => $value !== null),
        ];
    }
}

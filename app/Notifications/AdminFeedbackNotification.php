<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(private readonly Feedback $feedback) { $this->onQueue('default'); }
    public function via(object $notifiable): array { return $notifiable->notification_system_enabled ? ['database'] : []; }
    public function toArray(object $notifiable): array
    {
        return ['kind' => 'admin_feedback', 'title' => 'Yeni kullanıcı geri bildirimi', 'message' => $this->feedback->subject, 'feedback_id' => $this->feedback->id, 'reporter_id' => $this->feedback->user_id];
    }
}

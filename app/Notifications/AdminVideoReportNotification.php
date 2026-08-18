<?php

namespace App\Notifications;

use App\Models\VideoReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminVideoReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly VideoReport $report)
    {
        // Keep notification jobs on the queue the existing worker consumes.
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notification_system_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'admin_report',
            'title' => 'Yeni video şikayeti',
            'message' => 'Bir video şikayeti incelemenizi bekliyor.',
            'url' => route('admin.reports.index'),
            'report_id' => $this->report->id,
            'video_id' => $this->report->video_id,
            'reporter_id' => $this->report->reporter_id,
        ];
    }
}

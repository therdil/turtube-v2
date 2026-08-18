<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Illuminate\Notifications\DatabaseNotification */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kind' => (string) ($this->data['kind'] ?? 'system'),
            'title' => (string) ($this->data['title'] ?? 'TurTube bildirimi'),
            'message' => (string) ($this->data['message'] ?? ''),
            'url' => isset($this->data['url']) ? (string) $this->data['url'] : null,
            // Navigation targets are intentionally whitelisted. Never expose the
            // whole database-notification payload to API clients.
            'video_id' => isset($this->data['video_id']) ? (string) $this->data['video_id'] : null,
            'comment_id' => isset($this->data['comment_id']) ? (string) $this->data['comment_id'] : null,
            'channel_id' => isset($this->data['channel_id']) ? (string) $this->data['channel_id'] : null,
            'report_id' => isset($this->data['report_id']) ? (string) $this->data['report_id'] : null,
            'feedback_id' => isset($this->data['feedback_id']) ? (string) $this->data['feedback_id'] : null,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

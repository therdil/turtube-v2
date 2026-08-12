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
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

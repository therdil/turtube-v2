<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** This resource is restricted to the moderation API. */
class VideoReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'details' => $this->details,
            'status' => $this->status,
            'video_id' => $this->video_id,
            'video' => VideoResource::make($this->whenLoaded('video')),
            'reporter' => UserResource::make($this->whenLoaded('reporter')),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

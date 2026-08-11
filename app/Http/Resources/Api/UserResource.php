<?php

namespace App\Http\Resources\Api;

use App\Services\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // The existing platform uses `name` as its channel handle.
            'username' => $this->name,
            'name' => $this->name,
            'display_name' => $this->channel_name ?: $this->name,
            'description' => $this->channel_description,
            'avatar_url' => MediaUrl::absoluteFor($this->avatar),
            'banner_url' => MediaUrl::absoluteFor($this->banner),
            'verified' => (bool) $this->is_verified,
            'subscribers_count' => $this->whenCounted('subscribers'),
            'videos_count' => $this->when(isset($this->public_videos_count), (int) $this->public_videos_count),
            'shorts_count' => $this->when(isset($this->public_shorts_count), (int) $this->public_shorts_count),
            'joined_at' => $this->created_at?->toISOString(),
        ];
    }
}

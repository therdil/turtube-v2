<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** This resource is only returned by admin-protected routes. */
class ManagedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->name,
            'display_name' => $this->channel_name ?: $this->name,
            'email' => $this->email,
            'role' => $this->platformRole(),
            'is_admin' => (bool) $this->is_admin,
            'is_moderator' => (bool) $this->is_moderator,
            'has_premium_access' => $this->hasPremiumAccess(),
            'premium_until' => $this->premium_until?->toISOString(),
            'banned_at' => $this->banned_at?->toISOString(),
            'ban_reason' => $this->ban_reason,
            'avatar_url' => \App\Services\MediaUrl::absoluteFor($this->avatar),
            'banner_url' => \App\Services\MediaUrl::absoluteFor($this->banner),
            'subscribers_count' => $this->whenCounted('subscribers'),
            'videos_count' => $this->whenCounted('videos'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

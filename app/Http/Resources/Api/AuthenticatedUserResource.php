<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/**
 * The authenticated account representation. Keep account-only attributes out
 * of public channel responses.
 */
class AuthenticatedUserResource extends UserResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'role' => $this->platformRole(),
            'is_admin' => (bool) $this->is_admin,
            'is_moderator' => (bool) $this->is_moderator,
            'has_premium_access' => $this->hasPremiumAccess(),
            'notification_preferences' => [
                'likes_enabled' => (bool) $this->notification_likes_enabled,
                'comments_enabled' => (bool) $this->notification_comments_enabled,
                'subscribers_enabled' => (bool) $this->notification_subscribers_enabled,
                'system_enabled' => (bool) $this->notification_system_enabled,
            ],
            'privacy_settings' => [
                'channel_visibility' => $this->channel_visibility,
                'subscription_visibility' => (bool) $this->subscription_visibility,
                'playlist_visibility' => $this->playlist_visibility,
            ],
        ];
    }
}

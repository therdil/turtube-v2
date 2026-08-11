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
            'has_premium_access' => $this->hasPremiumAccess(),
        ];
    }
}

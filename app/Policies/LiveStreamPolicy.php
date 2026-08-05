<?php

namespace App\Policies;

use App\Models\LiveStream;
use App\Models\User;

class LiveStreamPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function manage(User $user, LiveStream $stream): bool
    {
        return $stream->user_id === $user->id;
    }
}

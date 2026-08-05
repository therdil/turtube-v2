<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;

class PlaylistPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function view(?User $user, Playlist $playlist): bool
    {
        return $playlist->is_public || $playlist->user_id === $user?->id;
    }

    public function update(User $user, Playlist $playlist): bool
    {
        return $playlist->user_id === $user->id;
    }

    public function delete(User $user, Playlist $playlist): bool
    {
        return $playlist->user_id === $user->id;
    }
}

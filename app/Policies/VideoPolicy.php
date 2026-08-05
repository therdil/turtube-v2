<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function view(?User $user, Video $video): bool
    {
        return $video->isVisibleTo($user) && $video->isPremiumAccessibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Video $video): bool
    {
        return $video->user_id === $user->id;
    }

    public function delete(User $user, Video $video): bool
    {
        return $video->user_id === $user->id;
    }

    public function report(User $user, Video $video): bool
    {
        return $video->user_id !== $user->id && $this->view($user, $video);
    }
}

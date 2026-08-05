<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Models\Video;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function create(User $user, Video $video): bool
    {
        return $video->isVisibleTo($user) && $video->isPremiumAccessibleTo($user);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $comment->video->user_id === $user->id;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function pin(User $user, Comment $comment): bool
    {
        return $comment->video->user_id === $user->id;
    }
}

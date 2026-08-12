<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Only an existing admin may alter another account's platform role. */
    public function manageRole(User $actor, User $target): bool
    {
        return $actor->is_admin && ! $actor->is($target);
    }
}

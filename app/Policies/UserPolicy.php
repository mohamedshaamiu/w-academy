<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manageCredentials(User $user): bool
    {
        return $user->isAdmin();
    }

    public function updateProfile(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }
}

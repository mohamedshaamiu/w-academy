<?php

namespace App\Policies;

use App\Models\Squad;
use App\Models\User;

class SquadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCoach();
    }

    public function view(User $user, Squad $squad): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoach()) {
            return $squad->head_coach_id === $user->coach?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Squad $squad): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Squad $squad): bool
    {
        return $user->isAdmin();
    }

    public function manageEnrolment(User $user, Squad $squad): bool
    {
        return $user->isAdmin();
    }
}

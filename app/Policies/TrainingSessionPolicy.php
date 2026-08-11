<?php

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\User;

class TrainingSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCoach();
    }

    public function view(User $user, TrainingSession $session): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoach()) {
            return $session->coach_id === $user->coach?->id;
        }

        if ($user->isGuardian()) {
            return $user->guardian
                ?->students()
                ->whereHas('squads', fn ($q) => $q->where('squads.id', $session->squad_id))
                ->exists() ?? false;
        }

        if ($user->isStudent()) {
            return $user->student
                ?->squads()
                ->wherePivot('is_active', true)
                ->where('squads.id', $session->squad_id)
                ->exists() ?? false;
        }

        return false;
    }

    public function manage(User $user, TrainingSession $session): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoach() && $session->coach_id === $user->coach?->id) {
            return ! $session->is_read_only_for_coach;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function markAttendance(User $user, TrainingSession $session): bool
    {
        return $this->manage($user, $session);
    }
}

<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCoach();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuardian()) {
            return $user->guardian
                ?->students()
                ->where('students.id', $student->id)
                ->exists() ?? false;
        }

        if ($user->isCoach()) {
            $squadIds = $user->coach?->squads()->pluck('squads.id') ?? collect();

            return $student->squads()
                ->wherePivot('is_active', true)
                ->whereIn('squads.id', $squadIds)
                ->exists();
        }

        if ($user->isStudent()) {
            return $user->student?->id === $student->id;
        }

        return false;
    }

    public function update(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    public function viewPhoto(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }
}

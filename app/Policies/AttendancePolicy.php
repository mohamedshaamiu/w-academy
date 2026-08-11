<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCoach()) {
            return $attendance->trainingSession->coach_id === $user->coach?->id;
        }

        if ($user->isGuardian()) {
            return $user->guardian
                ?->students()
                ->where('students.id', $attendance->student_id)
                ->exists() ?? false;
        }

        if ($user->isStudent()) {
            return $user->student?->id === $attendance->student_id;
        }

        return false;
    }

    public function mark(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin()
            || ($user->isCoach() && $attendance->trainingSession->coach_id === $user->coach?->id);
    }
}

<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CredentialService
{
    /**
     * Issue (or reset) a student's login. Returns the plaintext password —
     * the ONLY time it is ever available. Never logged, never persisted.
     */
    public function issueStudentCredentials(Student $student, User $actingAdmin): string
    {
        return DB::transaction(function () use ($student) {
            $password = Str::password((int) config('academy.generated_password_length.student'), symbols: false);

            if ($student->user) {
                $student->user->update([
                    'username' => $student->index_number,
                    'name' => $student->full_name,
                    'password' => Hash::make($password),
                    'must_change_password' => true,
                    'is_active' => true,
                ]);
            } else {
                $user = User::create([
                    'name' => $student->full_name,
                    'username' => $student->index_number,
                    'phone' => null,
                    'password' => Hash::make($password),
                    'must_change_password' => true,
                    'is_active' => true,
                ]);
                $user->assignRole('student');
                $student->update(['user_id' => $user->id]);
            }

            return $password;
        });
    }

    /**
     * Keep a student's linked login username in sync when their
     * index_number is changed.
     */
    public function syncStudentUsername(Student $student): void
    {
        if ($student->user) {
            $student->user->update(['username' => $student->index_number]);
        }
    }

    /**
     * Create a guardian's user + guardian profile, or link to an existing
     * user with that phone number. Returns [Guardian, ?password] — password
     * is null when linking to an existing account.
     *
     * @return array{0: Guardian, 1: ?string}
     */
    public function createOrLinkGuardian(array $attributes): array
    {
        return DB::transaction(function () use ($attributes) {
            $existingUser = User::withTrashed()->where('phone', $attributes['phone'])->first();

            if ($existingUser) {
                $guardian = $existingUser->guardian ?? Guardian::create([
                    'user_id' => $existingUser->id,
                    'national_id' => $attributes['national_id'] ?? null,
                    'address' => $attributes['address'],
                    'occupation' => $attributes['occupation'] ?? null,
                ]);

                if (! $existingUser->hasRole('guardian')) {
                    $existingUser->assignRole('guardian');
                }

                return [$guardian, null];
            }

            $password = Str::password((int) config('academy.generated_password_length.guardian'), symbols: false);

            $user = User::create([
                'name' => $attributes['name'],
                'username' => $attributes['phone'],
                'phone' => $attributes['phone'],
                'email' => $attributes['email'] ?? null,
                'password' => Hash::make($password),
                'must_change_password' => true,
                'is_active' => true,
            ]);
            $user->assignRole('guardian');

            $guardian = Guardian::create([
                'user_id' => $user->id,
                'national_id' => $attributes['national_id'] ?? null,
                'address' => $attributes['address'],
                'occupation' => $attributes['occupation'] ?? null,
            ]);

            return [$guardian, $password];
        });
    }

    public function resetGuardianCredentials(Guardian $guardian): string
    {
        $password = Str::password((int) config('academy.generated_password_length.guardian'), symbols: false);

        $guardian->user->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
            'is_active' => true,
        ]);

        return $password;
    }

    public function createCoach(array $attributes): array
    {
        return DB::transaction(function () use ($attributes) {
            $password = Str::password((int) config('academy.generated_password_length.coach'), symbols: false);

            $user = User::create([
                'name' => $attributes['name'],
                'username' => $attributes['phone'],
                'phone' => $attributes['phone'],
                'email' => $attributes['email'] ?? null,
                'password' => Hash::make($password),
                'must_change_password' => true,
                'is_active' => true,
            ]);
            $user->assignRole('coach');

            $coach = Coach::create([
                'user_id' => $user->id,
                'coach_no' => $this->nextCoachNumber(),
                'specialisation' => $attributes['specialisation'] ?? null,
                'joined_on' => $attributes['joined_on'] ?? now()->toDateString(),
            ]);

            return [$coach, $password];
        });
    }

    public function resetCoachCredentials(Coach $coach): string
    {
        $password = Str::password((int) config('academy.generated_password_length.coach'), symbols: false);

        $coach->user->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
            'is_active' => true,
        ]);

        return $password;
    }

    private function nextCoachNumber(): string
    {
        $next = (int) (Coach::query()->max('id')) + 1;

        return 'CO-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

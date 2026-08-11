<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentRegistrationService
{
    public function __construct(private readonly CredentialService $credentials) {}

    /**
     * Create a student record and link its guardian(s). Exactly one linked
     * guardian must be marked primary. New guardians are created inline via
     * CredentialService; existing guardians are linked by id.
     *
     * @param  array{index_number:string,full_name:string,date_of_birth:string,gender:string,school_name?:string,class_level?:string,address:string,photo_path?:string}  $studentAttributes
     * @param  array<int, array{guardian_id?:int, new_guardian?:array, relationship:string, is_primary:bool, receives_alerts?:bool}>  $guardianLinks
     */
    public function register(array $studentAttributes, array $guardianLinks, User $actingAdmin): Student
    {
        if (empty($guardianLinks)) {
            throw ValidationException::withMessages([
                'guardians' => __('student.validation.at_least_one_guardian'),
            ]);
        }

        $primaryCount = collect($guardianLinks)->where('is_primary', true)->count();
        if ($primaryCount !== 1) {
            throw ValidationException::withMessages([
                'guardians' => __('student.validation.exactly_one_primary_guardian'),
            ]);
        }

        return DB::transaction(function () use ($studentAttributes, $guardianLinks, $actingAdmin) {
            $student = Student::create([
                ...$studentAttributes,
                'index_number' => strtoupper(trim($studentAttributes['index_number'])),
                'status' => StudentStatus::Pending,
                'registered_at' => now(),
                'created_by' => $actingAdmin->id,
            ]);

            foreach ($guardianLinks as $link) {
                $guardian = isset($link['guardian_id'])
                    ? Guardian::findOrFail($link['guardian_id'])
                    : $this->credentials->createOrLinkGuardian($link['new_guardian'])[0];

                $student->guardians()->attach($guardian->id, [
                    'relationship' => $link['relationship'],
                    'is_primary' => $link['is_primary'],
                    'receives_alerts' => $link['receives_alerts'] ?? true,
                ]);
            }

            return $student->fresh();
        });
    }

    public function updateIndexNumber(Student $student, string $indexNumber): void
    {
        $student->update(['index_number' => strtoupper(trim($indexNumber))]);
        $this->credentials->syncStudentUsername($student);
    }

    public static function ageAsOfToday(string $dateOfBirth): int
    {
        return (int) Carbon::parse($dateOfBirth)->diffInYears(now());
    }
}

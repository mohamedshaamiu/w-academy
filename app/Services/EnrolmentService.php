<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Models\Squad;
use App\Models\SquadStudent;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrolmentService
{
    public function enrol(Student $student, Squad $squad, ?string $enrolledOn = null): SquadStudent
    {
        if ($student->status === StudentStatus::Inactive) {
            throw ValidationException::withMessages([
                'student' => __('squad.validation.inactive_student_cannot_enrol'),
            ]);
        }

        if ($squad->capacity !== null && $squad->active_student_count >= $squad->capacity) {
            throw ValidationException::withMessages([
                'squad' => __('squad.validation.squad_at_capacity'),
            ]);
        }

        return DB::transaction(function () use ($student, $squad, $enrolledOn) {
            $today = $enrolledOn ?? now()->toDateString();

            $current = $student->activeEnrolment;
            if ($current) {
                $current->update(['left_on' => $today, 'is_active' => false]);

                activity()
                    ->performedOn($student)
                    ->withProperties(['closed_squad_id' => $current->squad_id, 'new_squad_id' => $squad->id])
                    ->log('enrolment.superseded');
            }

            $enrolment = SquadStudent::create([
                'squad_id' => $squad->id,
                'student_id' => $student->id,
                'enrolled_on' => $today,
                'is_active' => true,
            ]);

            app(AgreementService::class)->maybeActivateStudent($student->fresh());

            return $enrolment;
        });
    }

    public function withdraw(Student $student, Squad $squad, ?string $leftOn = null): void
    {
        SquadStudent::where('squad_id', $squad->id)
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->update(['left_on' => $leftOn ?? now()->toDateString(), 'is_active' => false]);
    }
}

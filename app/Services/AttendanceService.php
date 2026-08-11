<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\StudentStatus;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * The roster for a session: students with an active enrolment in the
     * squad as of the session date. Suspended students are included but
     * their markable status is locked to `excused`.
     */
    public function roster(TrainingSession $session): Collection
    {
        return $session->squad->activeStudents()->get();
    }

    /**
     * @param  array<int, array{student_id:int, status:string, remark?:string}>  $entries
     */
    public function mark(TrainingSession $session, array $entries, User $markedBy): void
    {
        if (! in_array($session->status->value, ['in_progress', 'completed'], true)) {
            throw ValidationException::withMessages([
                'session' => __('attendance.validation.session_not_open'),
            ]);
        }

        $rosterIds = $this->roster($session)->pluck('id')->all();

        DB::transaction(function () use ($session, $entries, $markedBy, $rosterIds) {
            foreach ($entries as $entry) {
                if (! in_array($entry['student_id'], $rosterIds, true)) {
                    continue;
                }

                $student = Student::find($entry['student_id']);
                $status = $entry['status'];

                if ($student->status === StudentStatus::Suspended) {
                    $status = AttendanceStatus::Excused->value;
                }

                Attendance::updateOrCreate(
                    ['training_session_id' => $session->id, 'student_id' => $entry['student_id']],
                    [
                        'status' => $status,
                        'remark' => $entry['remark'] ?? null,
                        'marked_by' => $markedBy->id,
                        'marked_at' => now(),
                    ]
                );
            }
        });
    }
}

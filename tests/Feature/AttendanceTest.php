<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\EnrolmentService;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    private function sessionWithRoster(): array
    {
        $admin = User::factory()->create();
        $coachUser = User::factory()->create();
        $coachUser->assignRole('coach');
        $coach = Coach::factory()->create(['user_id' => $coachUser->id]);
        $squad = Squad::factory()->create(['head_coach_id' => $coach->id]);

        $student = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active]);
        app(EnrolmentService::class)->enrol($student, $squad);

        $session = TrainingSession::factory()->create(['squad_id' => $squad->id, 'coach_id' => $coach->id, 'status' => 'in_progress']);

        return [$session, $student, $coach, $coachUser];
    }

    public function test_only_assigned_coach_or_admin_can_mark_attendance(): void
    {
        [$session, $student] = $this->sessionWithRoster();

        $otherCoachUser = User::factory()->create();
        $otherCoachUser->assignRole('coach');
        Coach::factory()->create(['user_id' => $otherCoachUser->id]);

        $response = $this->actingAs($otherCoachUser)->post(route('coach.sessions.attendance.store', $session), [
            'entries' => [['student_id' => $student->id, 'status' => 'present']],
        ]);

        $response->assertForbidden();
    }

    public function test_suspended_student_cannot_be_marked_present(): void
    {
        [$session, $student, , $coachUser] = $this->sessionWithRoster();
        $student->update(['status' => StudentStatus::Suspended]);

        app(AttendanceService::class)->mark($session, [
            ['student_id' => $student->id, 'status' => 'present'],
        ], $coachUser);

        $this->assertDatabaseHas('attendances', [
            'training_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'excused',
        ]);
    }

    public function test_marking_attendance_twice_updates_not_duplicates(): void
    {
        [$session, $student, , $coachUser] = $this->sessionWithRoster();

        app(AttendanceService::class)->mark($session, [['student_id' => $student->id, 'status' => 'present']], $coachUser);
        app(AttendanceService::class)->mark($session, [['student_id' => $student->id, 'status' => 'late']], $coachUser);

        $this->assertSame(1, Attendance::where('training_session_id', $session->id)->where('student_id', $student->id)->count());
        $this->assertDatabaseHas('attendances', ['training_session_id' => $session->id, 'student_id' => $student->id, 'status' => 'late']);
    }
}

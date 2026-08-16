<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\Coach;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\EnrolmentService;
use Tests\TestCase;

/**
 * The rest of AttendanceTest hands the service integer student ids, which is
 * not what a browser sends. An HTML form posts every field as a string, so
 * `entries[0][student_id]` arrives as "1", not 1 — and the roster guard in
 * AttendanceService::mark() compares with a strict in_array().
 */
class AttendanceFormPostTest extends TestCase
{
    public function test_coach_can_mark_attendance_from_the_real_form_encoded_post(): void
    {
        [$session, $student, $coachUser] = $this->sessionWithRoster();

        $response = $this->actingAs($coachUser)->post(
            route('coach.sessions.attendance.store', $session),
            // Exactly what the browser submits: every value a string.
            ['entries' => [['student_id' => (string) $student->id, 'status' => 'present']]]
        );

        $response->assertRedirect(route('coach.sessions.show', $session));

        $this->assertDatabaseHas('attendances', [
            'training_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }

    /** @return array{0: TrainingSession, 1: Student, 2: User} */
    private function sessionWithRoster(): array
    {
        $admin = User::factory()->create();
        $coachUser = User::factory()->create();
        $coachUser->assignRole('coach');
        $coach = Coach::factory()->create(['user_id' => $coachUser->id]);
        $squad = Squad::factory()->create(['head_coach_id' => $coach->id]);

        $student = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active]);
        app(EnrolmentService::class)->enrol($student, $squad);

        $session = TrainingSession::factory()->create([
            'squad_id' => $squad->id,
            'coach_id' => $coach->id,
            'status' => 'in_progress',
        ]);

        return [$session, $student, $coachUser];
    }
}

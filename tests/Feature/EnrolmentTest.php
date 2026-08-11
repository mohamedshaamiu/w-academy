<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\Squad;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrolmentService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EnrolmentTest extends TestCase
{
    public function test_new_enrolment_closes_previous_active_enrolment(): void
    {
        $admin = User::factory()->create();
        $student = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active]);
        $squadA = Squad::factory()->create();
        $squadB = Squad::factory()->create();

        $enrolmentService = app(EnrolmentService::class);
        $enrolmentService->enrol($student, $squadA);
        $enrolmentService->enrol($student->fresh(), $squadB);

        $this->assertDatabaseHas('squad_student', ['squad_id' => $squadA->id, 'student_id' => $student->id, 'is_active' => false]);
        $this->assertDatabaseHas('squad_student', ['squad_id' => $squadB->id, 'student_id' => $student->id, 'is_active' => true]);
    }

    public function test_enrolment_blocked_when_squad_at_capacity(): void
    {
        $admin = User::factory()->create();
        $squad = Squad::factory()->create(['capacity' => 1]);
        $existing = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active]);
        app(EnrolmentService::class)->enrol($existing, $squad);

        $newStudent = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active]);

        $this->expectException(ValidationException::class);
        app(EnrolmentService::class)->enrol($newStudent, $squad);
    }
}

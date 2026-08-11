<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\AgreementTemplate;
use App\Models\Coach;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Models\User;
use App\Services\AgreementService;
use App\Services\EnrolmentService;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_guardian_cannot_view_another_guardians_child(): void
    {
        $admin = User::factory()->create();
        $student = Student::factory()->create(['created_by' => $admin->id]);

        $unrelatedGuardianUser = User::factory()->create();
        $unrelatedGuardianUser->assignRole('guardian');
        Guardian::factory()->create(['user_id' => $unrelatedGuardianUser->id]);

        $this->assertFalse($unrelatedGuardianUser->can('view', $student));
    }

    public function test_coach_cannot_view_student_outside_own_squad(): void
    {
        $admin = User::factory()->create();
        $coachUser = User::factory()->create();
        $coachUser->assignRole('coach');
        Coach::factory()->create(['user_id' => $coachUser->id]);

        $student = Student::factory()->create(['created_by' => $admin->id]);

        $this->assertFalse($coachUser->can('view', $student));
    }

    public function test_student_cannot_view_another_students_record(): void
    {
        $admin = User::factory()->create();
        $studentA = Student::factory()->create(['created_by' => $admin->id]);
        $studentB = Student::factory()->create(['created_by' => $admin->id]);

        $userA = User::factory()->create(['username' => $studentA->index_number]);
        $userA->assignRole('student');
        $studentA->update(['user_id' => $userA->id]);

        $this->assertFalse($userA->can('view', $studentB));
        $this->assertTrue($userA->can('view', $studentA));
    }

    public function test_student_schedule_exposes_no_teammate_names(): void
    {
        $admin = User::factory()->create();
        $coachUser = User::factory()->create();
        $coach = Coach::factory()->create(['user_id' => $coachUser->id]);
        $squad = Squad::factory()->create(['head_coach_id' => $coach->id]);

        $studentA = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active, 'full_name' => 'Teammate Alpha']);
        $studentB = Student::factory()->create(['created_by' => $admin->id, 'status' => StudentStatus::Active, 'full_name' => 'Teammate Beta']);
        app(EnrolmentService::class)->enrol($studentA, $squad);
        app(EnrolmentService::class)->enrol($studentB, $squad);

        $userB = User::factory()->create(['username' => $studentB->index_number]);
        $userB->assignRole('student');
        $studentB->update(['user_id' => $userB->id]);

        AgreementTemplate::factory()->create(['is_current' => true]);
        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);
        $studentB->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);
        app(AgreementService::class)->sign($studentB->fresh(), $guardian, 'Test Guardian', ['discipline_policy_acknowledged' => true], null, request());

        $response = $this->actingAs($userB)->get(route('student.schedule'));

        $response->assertOk();
        $response->assertDontSee('Teammate Alpha');
    }
}

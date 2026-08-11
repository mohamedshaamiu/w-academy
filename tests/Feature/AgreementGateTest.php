<?php

namespace Tests\Feature;

use App\Models\AgreementTemplate;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\AgreementService;
use Tests\TestCase;

class AgreementGateTest extends TestCase
{
    private function makeGuardianWithStudent(): array
    {
        $guardianUser = User::factory()->create(['username' => '7811100', 'phone' => '7811100']);
        $guardianUser->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);

        $admin = User::factory()->create();
        $student = Student::factory()->create(['created_by' => $admin->id]);
        $student->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);

        return [$guardianUser, $guardian, $student];
    }

    public function test_guardian_is_redirected_to_agreement_before_portal_access(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);
        [$guardianUser, , $student] = $this->makeGuardianWithStudent();

        $response = $this->actingAs($guardianUser)->get(route('guardian.dashboard'));

        $response->assertRedirect(route('agreement.show', $student));
    }

    public function test_student_is_blocked_until_guardian_signs(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);
        [, , $student] = $this->makeGuardianWithStudent();

        $studentUser = User::factory()->create(['username' => $student->index_number]);
        $studentUser->assignRole('student');
        $student->update(['user_id' => $studentUser->id]);

        $response = $this->actingAs($studentUser)->get(route('student.dashboard'));

        $response->assertForbidden();
    }

    public function test_student_cannot_sign_own_agreement(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);
        [, , $student] = $this->makeGuardianWithStudent();

        $studentUser = User::factory()->create(['username' => $student->index_number]);
        $studentUser->assignRole('student');
        $student->update(['user_id' => $studentUser->id]);

        $response = $this->actingAs($studentUser)->get(route('agreement.show', $student));

        $response->assertForbidden();
    }

    public function test_new_template_version_forces_resign(): void
    {
        $v1 = AgreementTemplate::factory()->create(['version' => 1, 'is_current' => true]);
        [$guardianUser, $guardian, $student] = $this->makeGuardianWithStudent();

        app(AgreementService::class)->sign($student, $guardian, 'Test Guardian', ['discipline_policy_acknowledged' => true], null, request());
        $this->assertTrue($student->fresh()->has_signed_current_agreement);

        $v2 = AgreementTemplate::factory()->create(['version' => 2]);
        app(AgreementService::class)->publish($v2);

        $this->assertFalse($student->fresh()->has_signed_current_agreement);
    }

    public function test_signature_stores_immutable_template_snapshot(): void
    {
        $template = AgreementTemplate::factory()->create(['is_current' => true, 'body_dv' => 'Original text']);
        [, $guardian, $student] = $this->makeGuardianWithStudent();

        app()->setLocale('dv');
        $signature = app(AgreementService::class)->sign($student, $guardian, 'Test Guardian', ['discipline_policy_acknowledged' => true], null, request());

        $template->update(['body_dv' => 'Changed text']);

        $this->assertSame('Original text', $signature->fresh()->template_snapshot);
    }

    public function test_snapshot_matches_the_locale_the_guardian_signed_in(): void
    {
        $template = AgreementTemplate::factory()->create([
            'is_current' => true,
            'body_dv' => 'Dhivehi body',
            'body_en' => 'English body',
        ]);
        [, $guardian, $student] = $this->makeGuardianWithStudent();

        app()->setLocale('dv');
        $signature = app(AgreementService::class)->sign($student, $guardian, 'Test Guardian', ['discipline_policy_acknowledged' => true], null, request());

        $this->assertSame('dv', $signature->signed_locale->value);
        $this->assertSame('Dhivehi body', $signature->template_snapshot);
    }
}

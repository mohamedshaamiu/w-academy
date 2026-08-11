<?php

namespace Tests\Feature;

use App\Enums\AgreementStatus;
use App\Enums\StudentStatus;
use App\Exceptions\SignatureIsImmutable;
use App\Models\AgreementSignature;
use App\Models\AgreementTemplate;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\AgreementService;
use Tests\TestCase;

/**
 * SPEC.md §8.3: "Signatures are immutable after creation. `template_snapshot`,
 * `signed_at`, `signed_locale`, `consents` and `agreement_template_id` may
 * never be updated — guard in the model's `updating` event. Corrections happen
 * by REVOKING and re-signing; `status`, `revoked_at` and `revoked_reason` are
 * writable only through `AgreementService::revoke()`."
 */
class AgreementSignatureTest extends TestCase
{
    /**
     * @return array{0: Student, 1: Guardian, 2: User, 3: AgreementSignature}
     */
    private function signedAgreement(): array
    {
        AgreementTemplate::factory()->create(['is_current' => true, 'body_dv' => 'Original snapshot body']);

        $guardianUser = User::factory()->create();
        $guardianUser->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);

        $student = Student::factory()->create([
            'created_by' => User::factory()->create()->id,
            'status' => StudentStatus::Active,
        ]);
        $student->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);

        $signature = app(AgreementService::class)->sign(
            student: $student->fresh(),
            guardian: $guardian,
            signatoryName: 'Test Guardian',
            consents: ['discipline_policy_acknowledged' => true],
            signatureImagePath: null,
            request: request(),
        );

        return [$student, $guardian, $guardianUser, $signature];
    }

    public function test_signed_agreement_fields_cannot_be_updated(): void
    {
        [, , , $signature] = $this->signedAgreement();

        $template = AgreementTemplate::factory()->create();

        $attempts = [
            'template_snapshot' => 'TAMPERED SNAPSHOT',
            'signed_at' => now()->subYears(5),
            'signed_locale' => 'en',
            'consents' => ['discipline_policy_acknowledged' => false],
            'agreement_template_id' => $template->id,
        ];

        foreach ($attempts as $column => $value) {
            $original = $signature->fresh()->getAttribute($column);

            try {
                $signature->fresh()->update([$column => $value]);
                $this->fail("SPEC.md §8.3: updating `{$column}` on a signed agreement must throw.");
            } catch (SignatureIsImmutable $e) {
                $this->assertStringContainsString($column, $e->getMessage());
            }

            $this->assertEquals(
                $original,
                $signature->fresh()->getAttribute($column),
                "`{$column}` must be unchanged after a rejected update."
            );
        }
    }

    public function test_revocation_is_the_only_mutation_path(): void
    {
        [, , , $signature] = $this->signedAgreement();

        // Writing the revocation columns directly is refused...
        foreach (['status' => AgreementStatus::Revoked, 'revoked_at' => now(), 'revoked_reason' => 'direct write'] as $column => $value) {
            try {
                $signature->fresh()->update([$column => $value]);
                $this->fail("SPEC.md §8.3: `{$column}` must only be writable through AgreementService::revoke().");
            } catch (SignatureIsImmutable $e) {
                $this->assertStringContainsString($column, $e->getMessage());
            }
        }

        $this->assertSame(AgreementStatus::Signed, $signature->fresh()->status);

        // ...and the service is the one path that works.
        $revoked = app(AgreementService::class)->revoke($signature->fresh(), 'Signed by the wrong guardian');

        $this->assertSame(AgreementStatus::Revoked, $revoked->status);
        $this->assertNotNull($revoked->revoked_at);
        $this->assertSame('Signed by the wrong guardian', $revoked->revoked_reason);

        // The immutable payload survived the revocation untouched.
        $this->assertSame('Original snapshot body', $revoked->template_snapshot);
        $this->assertSame(['discipline_policy_acknowledged' => true], $revoked->consents);
    }

    public function test_revoked_signature_no_longer_satisfies_the_gate(): void
    {
        [$student, , $guardianUser, $signature] = $this->signedAgreement();

        // Signed: the portal is reachable.
        $this->actingAs($guardianUser)->get(route('guardian.dashboard'))->assertOk();
        $this->assertTrue($student->fresh()->has_signed_current_agreement);

        app(AgreementService::class)->revoke($signature->fresh(), 'Superseded');

        // Revoked: the gate closes again and the record is preserved, not deleted.
        $this->assertFalse($student->fresh()->has_signed_current_agreement);
        $this->actingAs($guardianUser)
            ->get(route('guardian.dashboard'))
            ->assertRedirect(route('agreement.show', $student));

        $this->assertDatabaseHas('agreement_signatures', [
            'id' => $signature->id,
            'status' => AgreementStatus::Revoked->value,
        ]);
        $this->assertSame(1, AgreementSignature::query()->count());
    }
}

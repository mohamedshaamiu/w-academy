<?php

namespace Tests\Feature\Api;

use App\Models\AgreementTemplate;

/**
 * SPEC.md §7: GET /api/v1/children — "guardian only: students + agreement status".
 */
class ChildrenTest extends ApiTestCase
{
    public function test_guardian_receives_only_their_own_children(): void
    {
        [$guardianUser, $guardian, $ownChild] = $this->makeGuardianWithChild('Api Own Child');
        [, , $otherChild] = $this->makeGuardianWithChild('Api Other Child');
        $this->signAgreementFor($ownChild, $guardian);

        $response = $this->asApi($guardianUser)->getJson('/api/v1/children');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'index_number', 'full_name', 'status', 'has_signed_current_agreement']]]);

        $names = collect($response->json('data'))->pluck('full_name')->all();

        $this->assertContains($ownChild->full_name, $names);
        $this->assertNotContains($otherChild->full_name, $names);
        $this->assertCount(1, $names);
    }

    public function test_student_cannot_reach_the_children_endpoint(): void
    {
        [, , $student] = $this->makeGuardianWithChild();

        $studentUser = $this->makeUser('student', ['username' => $student->index_number]);
        $student->update(['user_id' => $studentUser->id]);

        $this->asApi($studentUser)->getJson('/api/v1/children')->assertForbidden();
    }

    public function test_coach_cannot_reach_the_children_endpoint(): void
    {
        [$coachUser] = $this->makeCoachWithSquad();

        $this->asApi($coachUser)->getJson('/api/v1/children')->assertForbidden();
    }

    public function test_children_endpoint_is_unreachable_without_a_token(): void
    {
        $this->getJson('/api/v1/children')->assertUnauthorized();
    }

    /** SPEC.md §8.3: the agreement gate covers the API too, not just the portal. */
    public function test_children_are_withheld_until_the_agreement_is_signed(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);
        [$guardianUser] = $this->makeGuardianWithChild('Ungated Child');

        $this->asApi($guardianUser)->getJson('/api/v1/children')->assertForbidden();
        $this->asApi($guardianUser)->getJson('/api/v1/schedule')->assertForbidden();
    }

    /** SPEC.md §8.1: a pending password change blocks every route. */
    public function test_children_are_withheld_while_a_password_change_is_pending(): void
    {
        [$guardianUser] = $this->makeGuardianWithChild();
        $token = $this->tokenFor($guardianUser);
        $guardianUser->update(['must_change_password' => true]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/children')
            ->assertForbidden();

        // /me stays reachable so the app can discover why it was refused.
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.must_change_password', true);
    }
}

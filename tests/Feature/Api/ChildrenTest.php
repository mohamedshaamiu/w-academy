<?php

namespace Tests\Feature\Api;

/**
 * SPEC.md §7: GET /api/v1/children — "guardian only: students + agreement status".
 */
class ChildrenTest extends ApiTestCase
{
    public function test_guardian_receives_only_their_own_children(): void
    {
        [$guardianUser, , $ownChild] = $this->makeGuardianWithChild('Api Own Child');
        [, , $otherChild] = $this->makeGuardianWithChild('Api Other Child');

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
}

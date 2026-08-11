<?php

namespace Tests\Feature\Api;

use App\Models\Squad;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\EnrolmentService;

/**
 * SPEC.md §7: GET /api/v1/schedule — "upcoming sessions scoped to the caller's role".
 */
class ScheduleTest extends ApiTestCase
{
    private function sessionFor(Squad $squad, ?int $coachId, string $venueEn): TrainingSession
    {
        return TrainingSession::factory()->create([
            'squad_id' => $squad->id,
            'coach_id' => $coachId,
            'venue_en' => $venueEn,
            'venue_dv' => $venueEn,
            'scheduled_start' => now()->addDay()->setTime(16, 0),
            'scheduled_end' => now()->addDay()->setTime(17, 30),
            'created_by' => User::factory()->create()->id,
        ]);
    }

    public function test_coach_sees_only_their_own_squads_sessions(): void
    {
        [$coachUser, $coach, $squad] = $this->makeCoachWithSquad('Own Squad');
        $this->sessionFor($squad, $coach->id, 'Own Venue');

        [, $otherCoach, $otherSquad] = $this->makeCoachWithSquad('Other Squad');
        $this->sessionFor($otherSquad, $otherCoach->id, 'Other Venue');

        $response = $this->asApi($coachUser)->getJson('/api/v1/schedule');

        $response->assertOk();
        $venues = collect($response->json('data'))->pluck('venue')->all();

        $this->assertContains('Own Venue', $venues);
        $this->assertNotContains('Other Venue', $venues);
    }

    public function test_guardian_sees_only_their_childs_sessions(): void
    {
        [$guardianUser, $guardian, $child] = $this->makeGuardianWithChild();
        [, $coach, $squad] = $this->makeCoachWithSquad('Child Squad');
        app(EnrolmentService::class)->enrol($child, $squad);
        $this->sessionFor($squad, $coach->id, 'Child Venue');
        $this->signAgreementFor($child, $guardian);

        [, $otherCoach, $otherSquad] = $this->makeCoachWithSquad('Unrelated Squad');
        $this->sessionFor($otherSquad, $otherCoach->id, 'Unrelated Venue');

        $response = $this->asApi($guardianUser)->getJson('/api/v1/schedule');

        $response->assertOk();
        $venues = collect($response->json('data'))->pluck('venue')->all();

        $this->assertContains('Child Venue', $venues);
        $this->assertNotContains('Unrelated Venue', $venues);
    }

    public function test_schedule_never_exposes_another_students_data(): void
    {
        [, , $teammate] = $this->makeGuardianWithChild('Schedule Teammate');
        [, $coach, $squad] = $this->makeCoachWithSquad('Shared Squad');
        app(EnrolmentService::class)->enrol($teammate, $squad);
        $this->sessionFor($squad, $coach->id, 'Shared Venue');

        // The caller is a real student in the same squad, with their own
        // guardian and a signed agreement — so they genuinely reach the
        // endpoint and the assertion below means something.
        [, $ownGuardian, $self] = $this->makeGuardianWithChild('Schedule Self');
        app(EnrolmentService::class)->enrol($self, $squad);
        $this->signAgreementFor($self, $ownGuardian);

        $studentUser = $this->makeUser('student', ['username' => $self->index_number]);
        $self->update(['user_id' => $studentUser->id]);

        $response = $this->asApi($studentUser)->getJson('/api/v1/schedule');

        $response->assertOk();
        $response->assertDontSee('Schedule Teammate');
    }

    public function test_schedule_is_unreachable_without_a_token(): void
    {
        $this->getJson('/api/v1/schedule')->assertUnauthorized();
    }
}

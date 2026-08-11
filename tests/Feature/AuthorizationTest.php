<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\AgreementTemplate;
use App\Models\Coach;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\AgreementService;
use App\Services\EnrolmentService;
use Tests\TestCase;

/**
 * SPEC.md §8.6 and §13: authorisation must be proven against real HTTP
 * responses. A `->can()` assertion only proves a Policy returns false in
 * isolation — it says nothing about whether any route consults that Policy.
 * Every test here drives the actual route and asserts on the response; the
 * policy-level assertion is kept alongside it as a second, narrower check.
 */
class AuthorizationTest extends TestCase
{
    /**
     * Satisfy the agreement gate for a student so these tests measure
     * authorisation rather than the gate. Deliberately signs a real agreement
     * instead of relying on "no current template", so the tests stay valid
     * once the gate is made to fail closed (BACKLOG.md P0-4).
     */
    private function signAgreementFor(Student $student, Guardian $guardian): void
    {
        app(AgreementService::class)->sign(
            student: $student->fresh(),
            guardian: $guardian,
            signatoryName: 'Test Guardian',
            consents: ['discipline_policy_acknowledged' => true],
            signatureImagePath: null,
            request: request(),
        );
    }

    /**
     * @return array{0: User, 1: Guardian}
     */
    private function makeGuardian(): array
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        return [$user, Guardian::factory()->create(['user_id' => $user->id])];
    }

    /**
     * @return array{0: User, 1: Coach}
     */
    private function makeCoach(): array
    {
        $user = User::factory()->create();
        $user->assignRole('coach');

        return [$user, Coach::factory()->create(['user_id' => $user->id])];
    }

    private function makeStudentLogin(Student $student): User
    {
        $user = User::factory()->create(['username' => $student->index_number]);
        $user->assignRole('student');
        $student->update(['user_id' => $user->id]);

        return $user;
    }

    private function makeStudent(string $name): Student
    {
        $admin = User::factory()->create();

        return Student::factory()->create([
            'created_by' => $admin->id,
            'full_name' => $name,
            'status' => StudentStatus::Active,
        ]);
    }

    public function test_guardian_cannot_view_another_guardians_child(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);

        [$guardianUserA, $guardianA] = $this->makeGuardian();
        $ownChild = $this->makeStudent('Own Child Alpha');
        $ownChild->guardians()->attach($guardianA->id, ['relationship' => 'father', 'is_primary' => true]);
        $this->signAgreementFor($ownChild, $guardianA);

        [, $guardianB] = $this->makeGuardian();
        $otherChild = $this->makeStudent('Other Child Bravo');
        $otherChild->guardians()->attach($guardianB->id, ['relationship' => 'mother', 'is_primary' => true]);

        // HTTP: the route itself must refuse the unrelated child.
        $this->actingAs($guardianUserA)
            ->get(route('guardian.children.show', $otherChild))
            ->assertForbidden();

        // HTTP: and must still serve their own child, so the 403 above is
        // authorisation and not a blanket failure of the route.
        $this->actingAs($guardianUserA)
            ->get(route('guardian.children.show', $ownChild))
            ->assertOk()
            ->assertSee('Own Child Alpha');

        // HTTP: the listing must not leak the other guardian's child either.
        $this->actingAs($guardianUserA)
            ->get(route('guardian.children.index'))
            ->assertOk()
            ->assertSee('Own Child Alpha')
            ->assertDontSee('Other Child Bravo');

        // Policy-level check retained as a second, narrower assertion.
        $this->assertFalse($guardianUserA->can('view', $otherChild));
        $this->assertTrue($guardianUserA->can('view', $ownChild));
    }

    public function test_coach_cannot_view_student_outside_own_squad(): void
    {
        [$coachUserA, $coachA] = $this->makeCoach();
        [, $coachB] = $this->makeCoach();

        $squadA = Squad::factory()->create(['head_coach_id' => $coachA->id, 'name_en' => 'Squad Alpha']);
        $squadB = Squad::factory()->create(['head_coach_id' => $coachB->id, 'name_en' => 'Squad Bravo']);

        $ownStudent = $this->makeStudent('Roster Insider');
        $outsideStudent = $this->makeStudent('Roster Outsider');

        app(EnrolmentService::class)->enrol($ownStudent, $squadA);
        app(EnrolmentService::class)->enrol($outsideStudent, $squadB);

        // HTTP: the other coach's squad detail — the page that would expose
        // that student's name, photo and status — must be refused.
        $this->actingAs($coachUserA)
            ->get(route('coach.squads.show', $squadB))
            ->assertForbidden();

        // HTTP: their own roster renders, and does not carry the outsider.
        $this->actingAs($coachUserA)
            ->get(route('coach.squads.show', $squadA))
            ->assertOk()
            ->assertSee('Roster Insider')
            ->assertDontSee('Roster Outsider');

        // HTTP: the squad index must not advertise the other coach's squad.
        $this->actingAs($coachUserA)
            ->get(route('coach.squads.index'))
            ->assertOk()
            ->assertDontSee('Squad Bravo');

        $this->assertFalse($coachUserA->can('view', $outsideStudent));
        $this->assertTrue($coachUserA->can('view', $ownStudent));
    }

    public function test_student_cannot_view_another_students_record(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);

        $studentA = $this->makeStudent('Pupil Alpha');
        $studentB = $this->makeStudent('Pupil Bravo');

        [, $guardian] = $this->makeGuardian();
        $studentA->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);
        $this->signAgreementFor($studentA, $guardian);

        $userA = $this->makeStudentLogin($studentA);

        // HTTP: every route that takes another student's id is refused.
        $this->actingAs($userA)
            ->get(route('guardian.children.show', $studentB))
            ->assertForbidden();

        $this->actingAs($userA)
            ->get(route('guardian.attendance', $studentB))
            ->assertForbidden();

        // HTTP: their own portal works and shows only their own record.
        $this->actingAs($userA)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Pupil Alpha')
            ->assertSee($studentA->index_number)
            ->assertDontSee('Pupil Bravo');

        $this->assertFalse($userA->can('view', $studentB));
        $this->assertTrue($userA->can('view', $studentA));
    }

    public function test_student_schedule_exposes_no_teammate_names(): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);

        [$coachUser, $coach] = $this->makeCoach();
        $coachUser->update(['name' => 'Coach Charlie']);
        $squad = Squad::factory()->create([
            'head_coach_id' => $coach->id,
            'venue_en' => 'Pitch Delta',
            'venue_dv' => 'Pitch Delta',
        ]);

        $teammate = $this->makeStudent('Teammate Alpha');
        $self = $this->makeStudent('Teammate Beta');

        app(EnrolmentService::class)->enrol($teammate, $squad);
        app(EnrolmentService::class)->enrol($self, $squad);

        TrainingSession::factory()->create([
            'squad_id' => $squad->id,
            'coach_id' => $coach->id,
            'scheduled_start' => now()->addDay()->setTime(16, 0),
            'scheduled_end' => now()->addDay()->setTime(17, 30),
            'venue_en' => 'Pitch Delta',
            'venue_dv' => 'Pitch Delta',
            'created_by' => User::factory()->create()->id,
        ]);

        [, $guardian] = $this->makeGuardian();
        $self->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);
        $this->signAgreementFor($self, $guardian);

        $response = $this->actingAs($this->makeStudentLogin($self))->get(route('student.schedule'));

        $response->assertOk();

        // Prove the page actually rendered the session before trusting
        // assertDontSee — otherwise an empty page would pass this test.
        $response->assertSee('Pitch Delta');
        $response->assertSee('Coach Charlie');

        $response->assertDontSee('Teammate Alpha');
        $response->assertDontSee($teammate->index_number);
    }
}

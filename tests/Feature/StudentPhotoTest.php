<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\AgreementTemplate;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\AgreementService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * SPEC.md §8.6: student photos live on the private disk and are served only
 * through a signed, policy-checked route. Views must build those URLs as
 * signed URLs from a single shared construction site, and a media failure
 * must be shown as a placeholder rather than suppressed with `onerror`.
 */
class StudentPhotoTest extends TestCase
{
    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function studentWithPhoto(string $name = 'Photo Subject'): Student
    {
        $student = Student::factory()->create([
            'created_by' => $this->makeAdmin()->id,
            'full_name' => $name,
            'status' => StudentStatus::Active,
        ]);

        $path = UploadedFile::fake()->image('portrait.jpg')->store('students', 'local');
        $student->update(['photo_path' => $path]);

        return $student->fresh();
    }

    /**
     * @return array{0: User, 1: Guardian}
     */
    private function makeGuardianFor(?Student $student = null): array
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);

        if ($student) {
            $student->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);
        }

        return [$user, $guardian];
    }

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

    public function test_signed_photo_url_renders_for_authorised_viewer(): void
    {
        Storage::fake('local');

        AgreementTemplate::factory()->create(['is_current' => true]);
        $student = $this->studentWithPhoto();
        [$guardianUser, $guardian] = $this->makeGuardianFor($student);
        $this->signAgreementFor($student, $guardian);

        // The rendered page must emit a SIGNED url, not a bare route() url.
        $page = $this->actingAs($guardianUser)->get(route('guardian.children.show', $student));
        $page->assertOk();

        $emitted = $this->photoUrlsIn($page->getContent());

        $this->assertNotEmpty($emitted, 'The child detail page emitted no student photo URL at all.');

        foreach ($emitted as $url) {
            $this->assertStringContainsString(
                'signature=',
                $url,
                "SPEC.md §8.6: photo URLs must be signed. Found an unsigned URL: {$url}"
            );
        }

        // And that url must actually serve the file.
        $this->actingAs($guardianUser)
            ->get(URL::signedRoute('students.photo', ['student' => $student->id]))
            ->assertOk();
    }

    public function test_unsigned_photo_url_is_rejected(): void
    {
        Storage::fake('local');

        $student = $this->studentWithPhoto();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('students.photo', $student))
            ->assertForbidden();
    }

    public function test_guardian_cannot_load_another_childs_photo(): void
    {
        Storage::fake('local');

        $student = $this->studentWithPhoto();
        $this->makeGuardianFor($student);

        [$strangerUser] = $this->makeGuardianFor();

        $this->actingAs($strangerUser)
            ->get(URL::signedRoute('students.photo', ['student' => $student->id]))
            ->assertForbidden();
    }

    public function test_photo_route_is_not_publicly_reachable(): void
    {
        Storage::fake('local');

        $student = $this->studentWithPhoto();

        // Even a correctly signed URL must not serve a minor's photo to a guest.
        $this->get(URL::signedRoute('students.photo', ['student' => $student->id]))
            ->assertRedirect(route('login'));

        $this->assertFalse(Storage::disk('local')->exists('public/'.$student->photo_path));
    }

    public function test_media_failure_is_shown_as_a_placeholder_not_suppressed(): void
    {
        Storage::fake('local');

        AgreementTemplate::factory()->create(['is_current' => true]);

        // A student with no photo at all: the page must still render, showing a
        // placeholder rather than a hidden broken image.
        $student = Student::factory()->create([
            'created_by' => $this->makeAdmin()->id,
            'full_name' => 'No Portrait',
            'status' => StudentStatus::Active,
        ]);
        [$guardianUser, $guardian] = $this->makeGuardianFor($student);
        $this->signAgreementFor($student, $guardian);

        $page = $this->actingAs($guardianUser)->get(route('guardian.children.show', $student));

        $page->assertOk();

        $this->assertSame(
            0,
            substr_count($page->getContent(), 'onerror'),
            'SPEC.md §8.6 forbids suppressing student media failures with onerror.'
        );

        $this->assertStringContainsString(
            'data-student-photo-placeholder',
            $page->getContent(),
            'SPEC.md §8.6: a missing photo must render a visible placeholder.'
        );
    }

    /**
     * Every student-photo URL a page emits, so failures report the URL rather
     * than the whole rendered document.
     *
     * @return array<int, string>
     */
    private function photoUrlsIn(string $html): array
    {
        preg_match_all('#/students/\d+/photo[^"\'\s>]*#', $html, $matches);

        return array_values(array_unique($matches[0]));
    }
}

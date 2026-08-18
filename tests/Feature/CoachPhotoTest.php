<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Coach photos feed the public homepage coach section. They live on the
 * private disk like student photos, but the route is deliberately
 * guest-reachable: only the `signed` middleware guards it, because the
 * photos are staff marketing content, not child data (SPEC.md §8.6 does
 * not apply to coaches).
 */
class CoachPhotoTest extends TestCase
{
    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function coachWithPhoto(): Coach
    {
        $coach = Coach::factory()->create();
        $coach->update(['photo_path' => UploadedFile::fake()->image('coach.jpg')->store('coaches', 'local')]);

        return $coach->fresh();
    }

    public function test_signed_coach_photo_url_serves_the_file_to_a_guest(): void
    {
        Storage::fake('local');
        $coach = $this->coachWithPhoto();

        $this->get(URL::signedRoute('coaches.photo', ['coach' => $coach->id]))
            ->assertOk();
    }

    public function test_unsigned_coach_photo_url_is_rejected(): void
    {
        Storage::fake('local');
        $coach = $this->coachWithPhoto();

        $this->get(route('coaches.photo', $coach))->assertForbidden();
    }

    public function test_coach_without_a_photo_is_a_404_even_when_signed(): void
    {
        Storage::fake('local');
        $coach = Coach::factory()->create();

        $this->get(URL::signedRoute('coaches.photo', ['coach' => $coach->id]))
            ->assertNotFound();
    }

    public function test_homepage_lists_coaches_with_signed_photo_urls_and_placeholders(): void
    {
        Storage::fake('local');

        $withPhoto = $this->coachWithPhoto();
        $withoutPhoto = Coach::factory()->create();

        $page = $this->get(route('home'));

        $page->assertOk();
        $page->assertSee($withPhoto->user->name);
        $page->assertSee($withoutPhoto->user->name);

        preg_match_all('#/coaches/\d+/photo[^"\'\s>]*#', $page->getContent(), $matches);
        $emitted = array_values(array_unique($matches[0]));

        $this->assertNotEmpty($emitted, 'The homepage emitted no coach photo URL for a coach that has one.');

        foreach ($emitted as $url) {
            $this->assertStringContainsString('signature=', $url, "Coach photo URLs must be signed. Found: {$url}");
        }

        // The photo-less coach must get a visible placeholder, never onerror.
        $this->assertSame(0, substr_count($page->getContent(), 'onerror'));
    }

    public function test_admin_can_upload_a_coach_photo_on_update(): void
    {
        Storage::fake('local');

        $coach = Coach::factory()->create();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.coaches.update', $coach), [
                'name' => $coach->user->name,
                'phone' => '7771234',
                'email' => $coach->user->email,
                'specialisation' => 'Goalkeeping',
                'joined_on' => '2024-01-01',
                'photo' => UploadedFile::fake()->image('portrait.jpg'),
            ])
            ->assertRedirect(route('admin.coaches.show', $coach));

        $coach->refresh();

        $this->assertNotNull($coach->photo_path);
        $this->assertTrue(Storage::disk('local')->exists($coach->photo_path));
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('local');

        $coach = Coach::factory()->create();

        $this->actingAs($this->makeAdmin())
            ->from(route('admin.coaches.edit', $coach))
            ->put(route('admin.coaches.update', $coach), [
                'name' => $coach->user->name,
                'phone' => '7771235',
                'email' => $coach->user->email,
                'joined_on' => '2024-01-01',
                'photo' => UploadedFile::fake()->create('malware.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($coach->fresh()->photo_path);
    }
}

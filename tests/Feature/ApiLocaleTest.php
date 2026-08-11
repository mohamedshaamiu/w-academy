<?php

namespace Tests\Feature;

use App\Models\TrainingSession;
use App\Models\User;
use Tests\Feature\Api\ApiTestCase;

/**
 * SPEC.md §3.2: "The API reads the `Accept-Language` header (`dv` or `en`)
 * and falls back to the authenticated user's `locale` column."
 * SPEC.md §7: API Resources resolve `*_dv`/`*_en` pairs to a single localised
 * field based on `Accept-Language`.
 */
class ApiLocaleTest extends ApiTestCase
{
    private function seedBilingualSession(): array
    {
        [$coachUser, $coach, $squad] = $this->makeCoachWithSquad('Squad In English', 'ސްކޮޑް ދިވެހި');

        TrainingSession::factory()->create([
            'squad_id' => $squad->id,
            'coach_id' => $coach->id,
            'venue_en' => 'Venue In English',
            'venue_dv' => 'ތަން ދިވެހި',
            'scheduled_start' => now()->addDay()->setTime(16, 0),
            'scheduled_end' => now()->addDay()->setTime(17, 30),
            'created_by' => User::factory()->create()->id,
        ]);

        return [$coachUser, $squad];
    }

    public function test_accept_language_switches_resource_language(): void
    {
        [$coachUser] = $this->seedBilingualSession();

        $english = $this->asApi($coachUser, 'en')->getJson('/api/v1/schedule');
        $dhivehi = $this->asApi($coachUser, 'dv')->getJson('/api/v1/schedule');

        $english->assertOk();
        $dhivehi->assertOk();

        $englishVenue = $english->json('data.0.venue');
        $dhivehiVenue = $dhivehi->json('data.0.venue');

        $this->assertSame('Venue In English', $englishVenue);
        $this->assertSame('ތަން ދިވެހި', $dhivehiVenue);
        $this->assertNotSame($englishVenue, $dhivehiVenue, 'Accept-Language must change the resolved content column.');

        $this->assertSame('Squad In English', $english->json('data.0.squad'));
        $this->assertSame('ސްކޮޑް ދިވެހި', $dhivehi->json('data.0.squad'));
    }

    public function test_api_locale_falls_back_to_the_users_locale_column(): void
    {
        [$coachUser] = $this->seedBilingualSession();
        $coachUser->update(['locale' => 'en']);

        // No Accept-Language header at all.
        $response = $this->asApi($coachUser)->getJson('/api/v1/schedule');

        $response->assertOk();
        $this->assertSame('Venue In English', $response->json('data.0.venue'));
    }

    public function test_unsupported_accept_language_falls_back_rather_than_adding_a_locale(): void
    {
        [$coachUser] = $this->seedBilingualSession();
        $coachUser->update(['locale' => 'dv']);

        $response = $this->asApi($coachUser, 'fr')->getJson('/api/v1/schedule');

        $response->assertOk();
        // SPEC.md §3.1: exactly two locales; anything else must not leak through.
        $this->assertSame('ތަން ދިވެހި', $response->json('data.0.venue'));
    }
}

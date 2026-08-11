<?php

namespace Tests\Feature\Api;

use Database\Seeders\FrameworkPillarSeeder;
use Database\Seeders\StrikeLevelSeeder;

/**
 * SPEC.md §7: GET /api/v1/framework — pillars + strike levels.
 * SPEC.md §6 grants `framework.view` to every role, so no role is refused;
 * the wrong-caller case is therefore the unauthenticated one.
 */
class FrameworkTest extends ApiTestCase
{
    public function test_framework_returns_pillars_and_strike_levels(): void
    {
        $this->seed(FrameworkPillarSeeder::class);
        $this->seed(StrikeLevelSeeder::class);

        $response = $this->asApi($this->makeUser('student'))->getJson('/api/v1/framework');

        $response->assertOk();
        $response->assertJsonStructure([
            'pillars' => [['id', 'code', 'name', 'description', 'icon']],
            'strike_levels' => [['id', 'level', 'label', 'type', 'action', 'parent_role']],
        ]);

        $this->assertNotEmpty($response->json('pillars'));
        $this->assertNotEmpty($response->json('strike_levels'));
    }

    public function test_framework_is_unreachable_without_a_token(): void
    {
        $this->getJson('/api/v1/framework')->assertUnauthorized();
    }
}

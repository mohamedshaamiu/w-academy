<?php

namespace Tests\Feature\Api;

use App\Enums\StudentStatus;
use App\Models\Coach;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

/**
 * Shared fixtures for the /api/v1 suite (SPEC.md §7).
 *
 * Tokens are minted through the real login endpoint and sent as Bearer
 * headers rather than via Sanctum::actingAs(), so these tests exercise the
 * actual token pipeline — which is what P0-2 broke.
 */
abstract class ApiTestCase extends TestCase
{
    protected function makeUser(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    protected function makeGuardianWithChild(string $childName = 'Api Child'): array
    {
        $user = $this->makeUser('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);

        $student = Student::factory()->create([
            'created_by' => $this->makeUser('admin')->id,
            'full_name' => $childName,
            'status' => StudentStatus::Active,
        ]);
        $student->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);

        return [$user, $guardian, $student];
    }

    protected function makeCoachWithSquad(string $squadEn = 'Api Squad', string $squadDv = 'ސްކޮޑް'): array
    {
        $user = $this->makeUser('coach');
        $coach = Coach::factory()->create(['user_id' => $user->id]);
        $squad = Squad::factory()->create([
            'head_coach_id' => $coach->id,
            'name_en' => $squadEn,
            'name_dv' => $squadDv,
        ]);

        return [$user, $coach, $squad];
    }

    /**
     * Log in over the real endpoint and return the bearer token.
     */
    protected function tokenFor(User $user, string $password = 'password'): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => $user->username,
            'password' => $password,
        ]);

        $response->assertOk();

        return $response->json('token');
    }

    protected function asApi(User $user, ?string $acceptLanguage = null): self
    {
        $headers = ['Authorization' => 'Bearer '.$this->tokenFor($user)];

        if ($acceptLanguage !== null) {
            $headers['Accept-Language'] = $acceptLanguage;
        }

        return $this->withHeaders($headers);
    }
}

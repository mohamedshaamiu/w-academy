<?php

namespace Tests\Feature\Api;

/**
 * SPEC.md §7: POST /api/v1/auth/login, POST /api/v1/auth/logout, GET /api/v1/me.
 */
class AuthTest extends ApiTestCase
{
    public function test_api_login_returns_a_bearer_token(): void
    {
        $user = $this->makeUser('guardian', ['username' => '7811100', 'phone' => '7811100']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => '7811100',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'user' => ['id', 'name', 'username', 'locale', 'role', 'must_change_password']]);
        $this->assertNotEmpty($response->json('token'));
        $this->assertSame('guardian', $response->json('user.role'));
        $this->assertSame($user->id, $response->json('user.id'));
    }

    public function test_api_login_rejects_bad_credentials(): void
    {
        $this->makeUser('guardian', ['username' => '7811101', 'phone' => '7811101']);

        $this->postJson('/api/v1/auth/login', [
            'username' => '7811101',
            'password' => 'not-the-password',
        ])->assertStatus(422)->assertJsonValidationErrors('username');
    }

    public function test_api_login_rejects_an_inactive_account(): void
    {
        $this->makeUser('guardian', ['username' => '7811102', 'phone' => '7811102', 'is_active' => false]);

        $this->postJson('/api/v1/auth/login', [
            'username' => '7811102',
            'password' => 'password',
        ])->assertStatus(422)->assertJsonValidationErrors('username');
    }

    public function test_api_logout_revokes_the_token(): void
    {
        $user = $this->makeUser('guardian');
        $token = $this->tokenFor($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Guards cache their resolved user for the lifetime of the test
        // application, so clear them to model a fresh request before checking
        // that the revoked token no longer authenticates.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_api_logout_is_unreachable_without_a_token(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
    }

    public function test_api_me_returns_role_and_forced_password_change(): void
    {
        $user = $this->makeUser('coach', ['must_change_password' => false]);

        $response = $this->asApi($user)->getJson('/api/v1/me');

        // UserResource is a JsonResource, so it wraps in `data` — consistent
        // with the collection endpoints (/children, /schedule).
        $response->assertOk();
        $this->assertSame($user->username, $response->json('data.username'));
        $this->assertSame('coach', $response->json('data.role'));
        $this->assertFalse($response->json('data.must_change_password'));
    }

    public function test_api_me_is_unreachable_without_a_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    /** SPEC.md §8.1: 5 failed attempts per username per minute. */
    public function test_api_login_is_rate_limited(): void
    {
        $this->makeUser('guardian', ['username' => '7811103', 'phone' => '7811103']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', ['username' => '7811103', 'password' => 'wrong'])
                ->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', ['username' => '7811103', 'password' => 'wrong'])
            ->assertStatus(429);

        // Even the correct password is refused while the lockout holds.
        $this->postJson('/api/v1/auth/login', ['username' => '7811103', 'password' => 'password'])
            ->assertStatus(429);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();

        // SPEC.md §9.4 / §8.1: exactly one identifier field, a password field,
        // a language toggle, and no register or forgot-password affordance.
        $response->assertSee('name="username"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee(__('auth.login.identifier'));
        $response->assertSee(route('locale.switch', 'en'), false);

        $response->assertDontSee('name="email"', false);
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'name="username"'),
            'SPEC.md §8.1 requires a single identifier field.'
        );
        foreach (['/register', '/forgot-password', '/reset-password'] as $forbidden) {
            $response->assertDontSee($forbidden, false);
        }
    }

    public function test_student_can_log_in_with_index_number(): void
    {
        $user = User::factory()->create(['username' => 'WA-1001']);
        $user->assignRole('student');
        Student::factory()->create(['index_number' => 'WA-1001', 'user_id' => $user->id, 'created_by' => $user->id]);

        $response = $this->post('/login', ['username' => 'WA-1001', 'password' => 'password']);

        $this->assertAuthenticatedAs($user->fresh());
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_guardian_can_log_in_with_phone_number(): void
    {
        $user = User::factory()->create(['username' => '7712233', 'phone' => '7712233']);
        $user->assignRole('guardian');
        Guardian::factory()->create(['user_id' => $user->id]);

        $response = $this->post('/login', ['username' => '7712233', 'password' => 'password']);

        $this->assertAuthenticatedAs($user->fresh());
        $response->assertRedirect(route('guardian.dashboard'));
    }

    public function test_index_number_lookup_is_case_and_space_insensitive(): void
    {
        $user = User::factory()->create(['username' => 'WA-1001']);
        $user->assignRole('student');
        Student::factory()->create(['index_number' => 'WA-1001', 'user_id' => $user->id, 'created_by' => $user->id]);

        $response = $this->post('/login', ['username' => ' wa-1001 ', 'password' => 'password']);

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create(['username' => '7712233', 'phone' => '7712233']);
        $user->assignRole('guardian');

        $response = $this->post('/login', ['username' => '7712233', 'password' => 'password']);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_first_login_forces_password_change(): void
    {
        $user = User::factory()->mustChangePassword()->create(['username' => '7712233', 'phone' => '7712233']);
        $user->assignRole('guardian');
        Guardian::factory()->create(['user_id' => $user->id]);

        $this->post('/login', ['username' => '7712233', 'password' => 'password']);

        $response = $this->get(route('guardian.dashboard'));

        $response->assertRedirect(route('password.change'));
    }

    public function test_no_public_registration_route_exists(): void
    {
        $this->assertFalse(Route::has('register'));
        $this->assertFalse(Route::has('password.request'));
        $this->assertFalse(Route::has('password.email'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }
}

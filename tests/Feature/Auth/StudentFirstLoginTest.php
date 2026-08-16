<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Reproduces the demo scenario: a student whose login was just issued by an
 * admin logs in for the first time and must land on the forced password
 * change screen with their session intact.
 */
class StudentFirstLoginTest extends TestCase
{
    public function test_student_first_login_keeps_the_session_on_the_password_change_screen(): void
    {
        $user = $this->makeStudentUser();

        $response = $this->post('/login', ['username' => 'WA-2026-001', 'password' => 'password']);

        $this->assertAuthenticatedAs($user->fresh());
        $response->assertRedirect(route('password.change'));

        // The session must survive onto the very next request.
        $this->get(route('password.change'))->assertOk();
    }

    /**
     * User::getAuthIdentifierName() is 'username', so Auth::id() is whatever
     * is in that column. For students that is an index number, which is not
     * an integer — but the Laravel skeleton declares `sessions.user_id` as a
     * foreignId (BIGINT UNSIGNED), and the database session driver writes
     * Auth::id() straight into it.
     *
     * On MySQL in strict mode that write threw inside
     * StartSession::terminate(), after the response had already been sent, so
     * the student's session was never persisted and they bounced back to the
     * login screen. Guardians, coaches and the admin were unaffected only
     * because their usernames are numeric phone numbers.
     *
     * SQLite is dynamically typed, so only the column type pins this down.
     */
    public function test_sessions_user_id_can_hold_a_non_numeric_auth_id(): void
    {
        $this->makeStudentUser();

        $this->post('/login', ['username' => 'WA-2026-001', 'password' => 'password']);

        $id = Auth::id();

        $this->assertSame('WA-2026-001', $id);
        $this->assertFalse(is_numeric($id), 'A student Auth::id() is an index number, not a number.');
        $this->assertNotSame(
            'integer',
            Schema::getColumnType('sessions', 'user_id'),
            'sessions.user_id must not be an integer column, or the database session driver cannot store a student Auth::id().'
        );
    }

    private function makeStudentUser(): User
    {
        $user = User::factory()->mustChangePassword()->create(['username' => 'WA-2026-001', 'phone' => null]);
        $user->assignRole('student');
        Student::factory()->create([
            'index_number' => 'WA-2026-001',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        return $user;
    }
}

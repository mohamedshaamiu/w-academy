<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Services\CredentialService;
use App\Services\StudentRegistrationService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CredentialTest extends TestCase
{
    public function test_issuing_login_sets_username_to_index_number(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = Student::factory()->create(['index_number' => 'WA-3001', 'created_by' => $admin->id]);

        app(CredentialService::class)->issueStudentCredentials($student, $admin);

        $this->assertDatabaseHas('users', ['username' => 'WA-3001']);
        $this->assertSame('WA-3001', $student->fresh()->user->username);
    }

    public function test_reissuing_resets_password_without_creating_second_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = Student::factory()->create(['index_number' => 'WA-3002', 'created_by' => $admin->id]);

        $first = app(CredentialService::class)->issueStudentCredentials($student, $admin);
        $userId = $student->fresh()->user_id;

        $second = app(CredentialService::class)->issueStudentCredentials($student->fresh(), $admin);

        $this->assertNotSame($first, $second);
        $this->assertSame($userId, $student->fresh()->user_id);
        $this->assertSame(1, User::where('username', 'WA-3002')->count());
    }

    public function test_changing_index_number_updates_linked_username(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = Student::factory()->create(['index_number' => 'WA-3003', 'created_by' => $admin->id]);
        app(CredentialService::class)->issueStudentCredentials($student, $admin);

        app(StudentRegistrationService::class)->updateIndexNumber($student->fresh(), 'WA-3003-NEW');

        $this->assertSame('WA-3003-NEW', $student->fresh()->user->username);
    }

    public function test_generated_password_is_not_persisted_in_plain_text(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = Student::factory()->create(['index_number' => 'WA-3004', 'created_by' => $admin->id]);

        $password = app(CredentialService::class)->issueStudentCredentials($student, $admin);

        $hashed = $student->fresh()->user->password;
        $this->assertNotSame($password, $hashed);
        $this->assertTrue(Hash::check($password, $hashed));
    }
}

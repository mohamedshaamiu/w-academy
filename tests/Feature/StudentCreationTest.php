<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class StudentCreationTest extends TestCase
{
    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_create_student_with_primary_guardian(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'index_number' => 'WA-2001',
            'full_name' => 'Test Student',
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'address' => 'Hulhumale',
            'guardians' => [
                [
                    'mode' => 'new',
                    'name' => 'Test Guardian',
                    'phone' => '7811111',
                    'address' => 'Hulhumale',
                    'relationship' => 'father',
                    'is_primary' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['index_number' => 'WA-2001']);
        $this->assertDatabaseHas('guardian_student', ['is_primary' => 1]);
    }

    public function test_student_creation_requires_at_least_one_guardian(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'index_number' => 'WA-2002',
            'full_name' => 'Test Student',
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'address' => 'Hulhumale',
            'guardians' => [],
        ]);

        $response->assertSessionHasErrors('guardians');
        $this->assertDatabaseMissing('students', ['index_number' => 'WA-2002']);
    }

    public function test_duplicate_index_number_is_rejected(): void
    {
        $admin = $this->admin();
        Student::factory()->create(['index_number' => 'WA-2003', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'index_number' => 'WA-2003',
            'full_name' => 'Test Student',
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'address' => 'Hulhumale',
            'guardians' => [
                ['mode' => 'new', 'name' => 'G', 'phone' => '7811112', 'address' => 'X', 'relationship' => 'father', 'is_primary' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('index_number');
    }

    public function test_age_outside_5_to_18_is_rejected(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'index_number' => 'WA-2004',
            'full_name' => 'Too Young',
            'date_of_birth' => now()->subYears(2)->toDateString(),
            'gender' => 'male',
            'address' => 'Hulhumale',
            'guardians' => [
                ['mode' => 'new', 'name' => 'G', 'phone' => '7811113', 'address' => 'X', 'relationship' => 'father', 'is_primary' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('date_of_birth');
    }

    public function test_thaana_input_is_accepted_and_stored_intact(): void
    {
        $admin = $this->admin();
        $thaanaName = 'އިބްރާހީމް ނާއިލް';

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'index_number' => 'WA-2005',
            'full_name' => $thaanaName,
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'address' => 'ހުޅުމާލެ',
            'guardians' => [
                ['mode' => 'new', 'name' => 'G', 'phone' => '7811114', 'address' => 'X', 'relationship' => 'father', 'is_primary' => 1],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['index_number' => 'WA-2005', 'full_name' => $thaanaName]);
    }

    public function test_non_admin_cannot_create_a_student(): void
    {
        $coach = User::factory()->create();
        $coach->assignRole('coach');

        $response = $this->actingAs($coach)->post(route('admin.students.store'), [
            'index_number' => 'WA-2006',
            'full_name' => 'Nope',
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'address' => 'Hulhumale',
            'guardians' => [
                ['mode' => 'new', 'name' => 'G', 'phone' => '7811115', 'address' => 'X', 'relationship' => 'father', 'is_primary' => 1],
            ],
        ]);

        $response->assertForbidden();
    }
}

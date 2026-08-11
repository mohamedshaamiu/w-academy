<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'index_number' => 'WA-'.fake()->unique()->numerify('####'),
            'user_id' => null,
            'full_name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-15 years', '-8 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
            'school_name' => 'Hulhumale School',
            'class_level' => 'Grade 6',
            'address' => fake()->address(),
            'status' => 'pending',
            'registered_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}

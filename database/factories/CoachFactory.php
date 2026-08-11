<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coach>
 */
class CoachFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'coach_no' => 'CO-'.fake()->unique()->numerify('####'),
            'specialisation' => 'Youth Development',
            'joined_on' => fake()->date(),
        ];
    }
}

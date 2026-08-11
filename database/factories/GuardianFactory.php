<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guardian>
 */
class GuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'national_id' => null,
            'address' => fake()->address(),
            'occupation' => fake()->jobTitle(),
        ];
    }
}

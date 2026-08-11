<?php

namespace Database\Factories;

use App\Models\Squad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Squad>
 */
class SquadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name_dv' => '[DV CONTENT PENDING]',
            'name_en' => fake()->unique()->words(2, true).' Squad',
            'age_group' => 'U-12',
            'head_coach_id' => null,
            'venue_dv' => null,
            'venue_en' => 'Training Ground 1',
            'training_days' => [1, 3, 5],
            'default_start_time' => '16:00',
            'default_end_time' => '17:30',
            'capacity' => null,
            'is_active' => true,
        ];
    }
}

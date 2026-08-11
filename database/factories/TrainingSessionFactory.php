<?php

namespace Database\Factories;

use App\Models\Squad;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+2 weeks');

        return [
            'squad_id' => Squad::factory(),
            'coach_id' => null,
            'scheduled_start' => $start,
            'scheduled_end' => (clone $start)->modify('+90 minutes'),
            'venue_dv' => null,
            'venue_en' => 'Training Ground 1',
            'status' => 'planned',
            'created_by' => User::factory(),
        ];
    }
}

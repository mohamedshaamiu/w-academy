<?php

namespace Tests\Feature;

use App\Models\Squad;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\SessionSchedulingService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SessionSchedulingTest extends TestCase
{
    public function test_generate_skips_dates_with_existing_sessions(): void
    {
        $admin = User::factory()->create();
        $squad = Squad::factory()->create([
            'training_days' => [1, 3, 5],
            'default_start_time' => '16:00',
            'default_end_time' => '17:30',
        ]);

        $monday = now()->next(1)->startOfDay();
        TrainingSession::factory()->create([
            'squad_id' => $squad->id,
            'scheduled_start' => $monday->copy()->setTime(16, 0),
            'scheduled_end' => $monday->copy()->setTime(17, 30),
        ]);

        $result = app(SessionSchedulingService::class)->generateFromSchedule(
            $squad,
            $monday->toDateString(),
            $monday->copy()->addWeek()->toDateString(),
            $admin,
        );

        $this->assertGreaterThanOrEqual(1, $result['skipped']);
    }

    public function test_overlapping_session_is_rejected(): void
    {
        $squad = Squad::factory()->create();
        $start = now()->addDay()->setTime(16, 0);
        $end = now()->addDay()->setTime(17, 30);

        TrainingSession::factory()->create([
            'squad_id' => $squad->id,
            'scheduled_start' => $start,
            'scheduled_end' => $end,
        ]);

        $this->expectException(ValidationException::class);

        app(SessionSchedulingService::class)->assertNoOverlap($squad, $start->copy()->addMinutes(30), $end->copy()->addMinutes(30));
    }
}

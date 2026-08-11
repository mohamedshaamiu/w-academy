<?php

namespace App\Services;

use App\Enums\SessionStatus;
use App\Models\Squad;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SessionSchedulingService
{
    /**
     * Generate planned sessions for a squad from its training_days +
     * default times across [$from, $to], skipping dates that already have
     * a session for that squad.
     *
     * @return array{created: int, skipped: int}
     */
    public function generateFromSchedule(Squad $squad, string $from, string $to, User $actingAdmin): array
    {
        if (! $squad->default_start_time || ! $squad->default_end_time) {
            throw ValidationException::withMessages([
                'squad' => __('session.validation.squad_missing_default_times'),
            ]);
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($squad, $from, $to, $actingAdmin, &$created, &$skipped) {
            $period = CarbonPeriod::create($from, $to);

            foreach ($period as $date) {
                if (! in_array((int) $date->dayOfWeekIso, $squad->training_days, true)) {
                    continue;
                }

                $existing = TrainingSession::where('squad_id', $squad->id)
                    ->whereDate('scheduled_start', $date->toDateString())
                    ->exists();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                $start = $date->copy()->setTimeFromTimeString($squad->default_start_time);
                $end = $date->copy()->setTimeFromTimeString($squad->default_end_time);

                $this->assertNoOverlap($squad, $start, $end);

                TrainingSession::create([
                    'squad_id' => $squad->id,
                    'coach_id' => $squad->head_coach_id,
                    'scheduled_start' => $start,
                    'scheduled_end' => $end,
                    'venue_dv' => $squad->venue_dv,
                    'venue_en' => $squad->venue_en,
                    'status' => SessionStatus::Planned,
                    'created_by' => $actingAdmin->id,
                ]);

                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function assertNoOverlap(Squad $squad, \DateTimeInterface $start, \DateTimeInterface $end, ?int $ignoreSessionId = null): void
    {
        if ($end <= $start) {
            throw ValidationException::withMessages([
                'scheduled_end' => __('session.validation.end_after_start'),
            ]);
        }

        $overlaps = TrainingSession::where('squad_id', $squad->id)
            ->when($ignoreSessionId, fn ($q) => $q->whereKeyNot($ignoreSessionId))
            ->where('status', '!=', SessionStatus::Cancelled->value)
            ->where('scheduled_start', '<', $end)
            ->where('scheduled_end', '>', $start)
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'scheduled_start' => __('session.validation.overlapping_session'),
            ]);
        }
    }

    public function cancel(TrainingSession $session, string $reason): void
    {
        $session->update([
            'status' => SessionStatus::Cancelled,
            'cancellation_reason' => $reason,
        ]);
    }
}

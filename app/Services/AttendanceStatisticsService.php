<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * The SOLE authority for any attendance count, rate or percentage
 * (SPEC.md §8.7).
 *
 * Every controller, report, dashboard and API resource calls this service. No
 * percentage is computed anywhere else — not in a controller, not in a Blade
 * file, not in a query scope — which is what guarantees that an admin, a
 * guardian and a student all read the same figure for the same student over
 * the same period. `AttendanceStatisticsTest::test_no_percentage_is_computed_outside_the_service`
 * enforces that by scanning the tree.
 *
 * How `late` and `excused` are treated is academy policy, read from
 * `config('academy.attendance')` and never hardcoded here.
 */
class AttendanceStatisticsService
{
    /**
     * Reduce a set of attendance records to attended / total / percentage.
     *
     * `total` is the denominator AFTER excluded statuses are removed, so it is
     * not necessarily the number of records passed in. `percentage` is null —
     * not zero — when nothing counts, so callers can distinguish "no data"
     * from "attended nothing".
     *
     * @param  iterable<int, Attendance>  $attendances
     * @return array{attended: int, total: int, excluded: int, percentage: int|null}
     */
    public function summarise(iterable $attendances): array
    {
        $attended = 0;
        $total = 0;
        $excluded = 0;

        foreach ($attendances as $attendance) {
            $status = $this->statusValue($attendance);

            if (in_array($status, $this->excludedStatuses(), true)) {
                $excluded++;

                continue;
            }

            $total++;

            if (in_array($status, $this->attendedStatuses(), true)) {
                $attended++;
            }
        }

        return [
            'attended' => $attended,
            'total' => $total,
            'excluded' => $excluded,
            'percentage' => $total > 0 ? (int) round($attended / $total * 100) : null,
        ];
    }

    /**
     * @param  iterable<int, Attendance>  $attendances
     */
    public function percentage(iterable $attendances): ?int
    {
        return $this->summarise($attendances)['percentage'];
    }

    /**
     * One student's figures over an inclusive date range.
     *
     * @return array{attended: int, total: int, excluded: int, percentage: int|null}
     */
    public function forStudentBetween(Student $student, CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->summarise(
            $student->attendances()->whereBetween('marked_at', [$from, $to])->get()
        );
    }

    /**
     * One student's figures for a calendar month, defaulting to the current one.
     *
     * @return array{attended: int, total: int, excluded: int, percentage: int|null}
     */
    public function forStudentInMonth(Student $student, ?CarbonInterface $reference = null): array
    {
        $reference ??= now();

        return $this->forStudentBetween(
            $student,
            $reference->copy()->startOfMonth(),
            $reference->copy()->endOfMonth(),
        );
    }

    /**
     * Group a flat set of records by student, one summary per student.
     *
     * @param  iterable<int, Attendance>  $attendances
     * @return Collection<int, array{student: Student, attended: int, total: int, excluded: int, percentage: int|null}>
     */
    public function summariseByStudent(iterable $attendances): Collection
    {
        return Collection::make($attendances)
            ->groupBy('student_id')
            ->map(fn (Collection $records) => $this->summarise($records) + [
                'student' => $records->first()->student,
            ]);
    }

    private function statusValue(Attendance $attendance): string
    {
        return $attendance->status instanceof AttendanceStatus
            ? $attendance->status->value
            : (string) $attendance->status;
    }

    /**
     * @return array<int, string>
     */
    private function attendedStatuses(): array
    {
        return (array) config('academy.attendance.attended_statuses', []);
    }

    /**
     * @return array<int, string>
     */
    private function excludedStatuses(): array
    {
        return (array) config('academy.attendance.excluded_statuses', []);
    }
}

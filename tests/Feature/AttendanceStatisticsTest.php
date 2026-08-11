<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\AgreementTemplate;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\AgreementService;
use App\Services\AttendanceStatisticsService;
use App\Services\EnrolmentService;
use Tests\TestCase;

/**
 * SPEC.md §8.7: `AttendanceStatisticsService` is the SOLE authority for any
 * attendance count, rate or percentage. All roles must see an identical figure
 * for the same student over the same period, and the treatment of `late` and
 * `excused` is read from `config('academy.attendance')`, never hardcoded.
 */
class AttendanceStatisticsTest extends TestCase
{
    private Student $student;

    private User $adminUser;

    private User $guardianUser;

    private User $studentUser;

    /**
     * One student, one squad, and a known attendance mix inside the current
     * month, reachable by an admin, their guardian and the student themselves.
     */
    private function seedScenario(array $statuses): void
    {
        AgreementTemplate::factory()->create(['is_current' => true]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $coach = Coach::factory()->create();
        $squad = Squad::factory()->create(['head_coach_id' => $coach->id]);

        $this->student = Student::factory()->create([
            'created_by' => $this->adminUser->id,
            'full_name' => 'Stats Subject',
            'status' => StudentStatus::Active,
        ]);

        app(EnrolmentService::class)->enrol($this->student, $squad);

        $this->guardianUser = User::factory()->create();
        $this->guardianUser->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $this->guardianUser->id]);
        $this->student->guardians()->attach($guardian->id, ['relationship' => 'father', 'is_primary' => true]);

        app(AgreementService::class)->sign(
            student: $this->student->fresh(),
            guardian: $guardian,
            signatoryName: 'Test Guardian',
            consents: ['discipline_policy_acknowledged' => true],
            signatureImagePath: null,
            request: request(),
        );

        $this->studentUser = User::factory()->create(['username' => $this->student->index_number]);
        $this->studentUser->assignRole('student');
        $this->student->update(['user_id' => $this->studentUser->id]);

        foreach ($statuses as $index => $status) {
            $session = TrainingSession::factory()->create([
                'squad_id' => $squad->id,
                'coach_id' => $coach->id,
                'created_by' => $this->adminUser->id,
            ]);

            Attendance::factory()->create([
                'training_session_id' => $session->id,
                'student_id' => $this->student->id,
                'status' => $status,
                'marked_by' => $coach->user_id,
                'marked_at' => now()->startOfMonth()->addDays($index)->setTime(17, 0),
            ]);
        }
    }

    public function test_all_roles_see_identical_percentage_for_same_student(): void
    {
        // present, present, late, absent, excused
        // attended = 3 (present + late), denominator = 4 (excused excluded) -> 75%
        $this->seedScenario(['present', 'present', 'late', 'absent', 'excused']);

        $expected = '75%';

        $this->actingAs($this->adminUser)
            ->get(route('admin.reports.attendance'))
            ->assertOk()
            ->assertSee('Stats Subject')
            ->assertSee($expected);

        $this->actingAs($this->guardianUser)
            ->get(route('guardian.dashboard'))
            ->assertOk()
            ->assertSee($expected);

        $this->actingAs($this->studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee($expected);

        // And the service itself agrees with what every screen rendered.
        $this->assertSame(
            75,
            app(AttendanceStatisticsService::class)->percentage($this->student->attendances()->get())
        );
    }

    public function test_late_treatment_follows_config(): void
    {
        $this->seedScenario(['present', 'late']);

        $service = app(AttendanceStatisticsService::class);

        // Default (confirmed by the academy): `late` counts as attended.
        config(['academy.attendance.attended_statuses' => ['present', 'late']]);
        $this->assertSame(100, $service->percentage($this->student->attendances()->get()));

        // Flip the policy: `late` no longer counts as attended.
        config(['academy.attendance.attended_statuses' => ['present']]);
        $this->assertSame(50, $service->percentage($this->student->attendances()->get()));
    }

    public function test_excused_is_excluded_from_denominator(): void
    {
        $this->seedScenario(['present', 'excused']);

        $service = app(AttendanceStatisticsService::class);

        // Default: `excused` leaves the denominator entirely -> 1/1 = 100%.
        config(['academy.attendance.excluded_statuses' => ['excused']]);
        $summary = $service->summarise($this->student->attendances()->get());

        $this->assertSame(1, $summary['attended']);
        $this->assertSame(1, $summary['total']);
        $this->assertSame(100, $summary['percentage']);

        // Include it again and the same data reads 1/2 = 50%.
        config(['academy.attendance.excluded_statuses' => []]);
        $this->assertSame(50, $service->percentage($this->student->attendances()->get()));
    }

    public function test_no_percentage_is_computed_outside_the_service(): void
    {
        $offenders = [];
        $allowed = 'app/Services/AttendanceStatisticsService.php';

        foreach ($this->scannableFiles() as $file) {
            $relative = str_replace('\\', '/', substr($file, strlen(base_path()) + 1));

            if ($relative === $allowed) {
                continue;
            }

            foreach (file($file) as $number => $line) {
                // A percentage is a rounding or a *100 scaling. Either outside
                // the service means a second source of truth (SPEC.md §8.7).
                if (preg_match('/\bround\s*\(|\*\s*100\b/', $line)) {
                    $offenders[] = $relative.':'.($number + 1).' — '.trim($line);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "SPEC.md §8.7: attendance percentages must only be computed in AttendanceStatisticsService.\n"
            .implode("\n", $offenders)
        );
    }

    /**
     * @return array<int, string>
     */
    private function scannableFiles(): array
    {
        $files = [];

        foreach ([app_path(), resource_path('views')] as $root) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php'], true)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }
}

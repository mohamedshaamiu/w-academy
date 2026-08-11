<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Enums\StudentStatus;
use App\Models\Coach;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Realistic-shaped Maldivian demo data for local development only.
     * Never run in production seeding.
     */
    public function run(): void
    {
        $admin = User::where('username', '7900000')->first();

        $coaches = collect([
            ['name' => 'Ahmed Shifau', 'phone' => '7712233'],
            ['name' => 'Hassan Rasheed', 'phone' => '7723344'],
        ])->map(function (array $data, int $i) {
            $user = User::updateOrCreate(
                ['username' => $data['phone']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('Coach@2026'),
                    'locale' => 'dv',
                    'must_change_password' => true,
                    'is_active' => true,
                ]
            );
            if (! $user->hasRole('coach')) {
                $user->assignRole('coach');
            }

            return Coach::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'coach_no' => 'CO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'specialisation' => 'Youth Development',
                    'joined_on' => now()->subYears(2)->toDateString(),
                ]
            );
        });

        $squadU12 = Squad::updateOrCreate(
            ['name_en' => 'Hurricanes U-12'],
            [
                'name_dv' => '[DV CONTENT PENDING]',
                'age_group' => 'U-12',
                'head_coach_id' => $coaches[0]->id,
                'venue_dv' => '[DV CONTENT PENDING]',
                'venue_en' => 'Hulhumalé Turf Ground 1',
                'training_days' => [1, 3, 5],
                'default_start_time' => '16:00',
                'default_end_time' => '17:30',
                'capacity' => 24,
                'is_active' => true,
            ]
        );

        $squadU15 = Squad::updateOrCreate(
            ['name_en' => 'Falcons U-15'],
            [
                'name_dv' => '[DV CONTENT PENDING]',
                'age_group' => 'U-15',
                'head_coach_id' => $coaches[1]->id,
                'venue_dv' => '[DV CONTENT PENDING]',
                'venue_en' => 'Malé National Football Stadium (Training Pitch)',
                'training_days' => [2, 4],
                'default_start_time' => '16:30',
                'default_end_time' => '18:00',
                'capacity' => 20,
                'is_active' => true,
            ]
        );

        $students = [
            ['full_name' => 'Ibrahim Naail', 'index' => 'WA-2026-001', 'dob' => '2014-03-12', 'gender' => 'male', 'squad' => $squadU12, 'guardian' => ['name' => 'Fathimath Nazeera', 'phone' => '7811122']],
            ['full_name' => 'Aminath Zaha', 'index' => 'WA-2026-002', 'dob' => '2013-07-02', 'gender' => 'female', 'squad' => $squadU12, 'guardian' => ['name' => 'Mohamed Rasheed', 'phone' => '7822233']],
            ['full_name' => 'Hussain Yoosuf', 'index' => 'WA-2026-003', 'dob' => '2011-11-20', 'gender' => 'male', 'squad' => $squadU15, 'guardian' => ['name' => 'Aishath Shafeega', 'phone' => '7833344']],
            ['full_name' => 'Aishath Mariyam', 'index' => 'WA-2026-004', 'dob' => '2012-01-08', 'gender' => 'female', 'squad' => $squadU15, 'guardian' => ['name' => 'Ali Waheed', 'phone' => '7844455']],
        ];

        foreach ($students as $i => $data) {
            $guardianUser = User::updateOrCreate(
                ['username' => $data['guardian']['phone']],
                [
                    'name' => $data['guardian']['name'],
                    'phone' => $data['guardian']['phone'],
                    'password' => Hash::make('Guardian@2026'),
                    'locale' => 'dv',
                    'must_change_password' => true,
                    'is_active' => true,
                ]
            );
            if (! $guardianUser->hasRole('guardian')) {
                $guardianUser->assignRole('guardian');
            }
            $guardian = Guardian::updateOrCreate(
                ['user_id' => $guardianUser->id],
                ['address' => 'Hulhumalé, Malé, Maldives']
            );

            $student = Student::updateOrCreate(
                ['index_number' => $data['index']],
                [
                    'full_name' => $data['full_name'],
                    'date_of_birth' => $data['dob'],
                    'gender' => $data['gender'],
                    'school_name' => 'Hulhumalé School',
                    'class_level' => 'Grade '.(6 + $i),
                    'address' => 'Hulhumalé, Malé, Maldives',
                    'status' => StudentStatus::Active,
                    'registered_at' => now()->subMonths(2),
                    'created_by' => $admin->id,
                ]
            );

            if (! $student->guardians()->where('guardians.id', $guardian->id)->exists()) {
                $student->guardians()->attach($guardian->id, [
                    'relationship' => 'parent',
                    'is_primary' => true,
                    'receives_alerts' => true,
                ]);
            }

            if (! $student->activeEnrolment) {
                $student->squads()->attach($data['squad']->id, [
                    'enrolled_on' => now()->subMonths(2)->toDateString(),
                    'is_active' => true,
                ]);
            }
        }

        foreach ([$squadU12, $squadU15] as $squad) {
            $session = TrainingSession::updateOrCreate(
                [
                    'squad_id' => $squad->id,
                    'scheduled_start' => now()->subDays(3)->setTimeFromTimeString($squad->default_start_time),
                ],
                [
                    'coach_id' => $squad->head_coach_id,
                    'scheduled_end' => now()->subDays(3)->setTimeFromTimeString($squad->default_end_time),
                    'venue_dv' => $squad->venue_dv,
                    'venue_en' => $squad->venue_en,
                    'status' => SessionStatus::Completed,
                    'created_by' => $admin->id,
                ]
            );

            foreach ($squad->activeStudents as $student) {
                $session->attendances()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'status' => AttendanceStatus::Present,
                        'marked_by' => $admin->id,
                        'marked_at' => $session->scheduled_end,
                    ]
                );
            }
        }
    }
}

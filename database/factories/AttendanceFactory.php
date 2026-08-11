<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'training_session_id' => TrainingSession::factory(),
            'student_id' => Student::factory(),
            'status' => 'present',
            'remark' => null,
            'marked_by' => User::factory(),
            'marked_at' => now(),
        ];
    }
}

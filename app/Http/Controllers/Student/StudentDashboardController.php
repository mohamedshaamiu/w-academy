<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AttendanceStatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __construct(private readonly AttendanceStatisticsService $statistics) {}

    public function index(Request $request): View
    {
        $student = $request->user()->student;
        $squad = $student->activeEnrolment?->squad;

        $nextSession = $squad
            ?->trainingSessions()
            ->where('scheduled_start', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_start')
            ->first();

        return view('student.dashboard', [
            'student' => $student,
            'squad' => $squad,
            'next_session' => $nextSession,
            'attendance_percentage' => $this->statistics->forStudentInMonth($student)['percentage'],
        ]);
    }
}

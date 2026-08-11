<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Services\AttendanceStatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianDashboardController extends Controller
{
    public function __construct(private readonly AttendanceStatisticsService $statistics) {}

    public function index(Request $request): View
    {
        $guardian = $request->user()->guardian;

        $students = $guardian->students()
            ->with(['activeEnrolment.squad'])
            ->get()
            ->map(function ($student) {
                $enrolment = $student->activeEnrolment;
                $nextSession = $enrolment?->squad
                    ?->trainingSessions()
                    ->where('scheduled_start', '>=', now())
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('scheduled_start')
                    ->first();

                return [
                    'student' => $student,
                    'squad' => $enrolment?->squad,
                    'next_session' => $nextSession,
                    'attendance_percentage' => $this->statistics->forStudentInMonth($student)['percentage'],
                ];
            });

        return view('guardian.dashboard', ['cards' => $students]);
    }
}

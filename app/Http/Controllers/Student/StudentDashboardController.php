<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
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

        $monthAttendance = $student->attendances()
            ->whereMonth('marked_at', now()->month)
            ->whereYear('marked_at', now()->year)
            ->get();

        $percentage = $monthAttendance->isEmpty()
            ? null
            : (int) round($monthAttendance->where('status', 'present')->count() / $monthAttendance->count() * 100);

        return view('student.dashboard', [
            'student' => $student,
            'squad' => $squad,
            'next_session' => $nextSession,
            'attendance_percentage' => $percentage,
        ]);
    }
}

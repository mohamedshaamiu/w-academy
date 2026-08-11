<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianDashboardController extends Controller
{
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

                $monthAttendance = $student->attendances()
                    ->whereMonth('marked_at', now()->month)
                    ->whereYear('marked_at', now()->year)
                    ->get();

                $percentage = $monthAttendance->isEmpty()
                    ? null
                    : (int) round($monthAttendance->where('status', 'present')->count() / $monthAttendance->count() * 100);

                return [
                    'student' => $student,
                    'squad' => $enrolment?->squad,
                    'next_session' => $nextSession,
                    'attendance_percentage' => $percentage,
                ];
            });

        return view('guardian.dashboard', ['cards' => $students]);
    }
}

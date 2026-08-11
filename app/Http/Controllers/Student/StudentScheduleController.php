<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentScheduleController extends Controller
{
    /**
     * Own upcoming sessions only: time, venue, coach. Never a teammate
     * roster — the query never touches other students.
     */
    public function index(Request $request): View
    {
        $squad = $request->user()->student->activeEnrolment?->squad;

        $sessions = $squad
            ? TrainingSession::where('squad_id', $squad->id)
                ->where('scheduled_start', '>=', now()->startOfDay())
                ->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_start')
                ->with('coach.user')
                ->get()
                ->groupBy(fn (TrainingSession $session) => $session->scheduled_start->toDateString())
            : collect();

        return view('student.schedule', ['sessionsByDate' => $sessions]);
    }
}

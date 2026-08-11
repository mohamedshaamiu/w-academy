<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $coach = $request->user()->coach;

        $todaySessions = TrainingSession::where('coach_id', $coach->id)
            ->whereBetween('scheduled_start', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('scheduled_start')
            ->with('squad')
            ->get();

        $squads = $coach->squads()->withCount('activeStudents')->get();

        return view('coach.dashboard', [
            'todaySessions' => $todaySessions,
            'squads' => $squads,
            'totalStudents' => $squads->sum('active_students_count'),
        ]);
    }
}

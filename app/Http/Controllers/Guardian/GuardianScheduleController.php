<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $squadIds = $request->user()->guardian->students()
            ->with('activeEnrolment')
            ->get()
            ->pluck('activeEnrolment.squad_id')
            ->filter()
            ->unique();

        $sessions = TrainingSession::whereIn('squad_id', $squadIds)
            ->where('scheduled_start', '>=', now()->startOfDay())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_start')
            ->with('squad', 'coach.user')
            ->get()
            ->groupBy(fn (TrainingSession $session) => $session->scheduled_start->toDateString());

        return view('guardian.schedule', ['sessionsByDate' => $sessions]);
    }
}

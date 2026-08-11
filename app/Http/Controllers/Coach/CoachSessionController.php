<?php

namespace App\Http\Controllers\Coach;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachSessionController extends Controller
{
    public function index(Request $request): View
    {
        return view('coach.sessions', [
            'sessions' => TrainingSession::where('coach_id', $request->user()->coach->id)
                ->orderByDesc('scheduled_start')
                ->with('squad')
                ->paginate(20),
        ]);
    }

    public function show(TrainingSession $session): View
    {
        $this->authorize('view', $session);

        return view('coach.session-detail', ['session' => $session->load('squad', 'attendances.student')]);
    }

    public function start(TrainingSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $session->update(['status' => SessionStatus::InProgress]);

        return back()->with('status', __('session.started'));
    }

    public function complete(TrainingSession $session): RedirectResponse
    {
        $this->authorize('manage', $session);

        $session->update(['status' => SessionStatus::Completed]);

        return back()->with('status', __('session.completed_notice'));
    }
}

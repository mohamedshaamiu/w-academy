<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreAttendanceRequest;
use App\Models\TrainingSession;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    public function edit(TrainingSession $session): View
    {
        $this->authorize('markAttendance', $session);

        return view('coach.attendance-mark', [
            'session' => $session->load('squad'),
            'roster' => $this->attendance->roster($session),
            'existing' => $session->attendances()->get()->keyBy('student_id'),
        ]);
    }

    public function store(StoreAttendanceRequest $request, TrainingSession $session): RedirectResponse
    {
        $this->authorize('markAttendance', $session);

        $this->attendance->mark($session, $request->array('entries'), $request->user());

        return redirect()->route('coach.sessions.show', $session)->with('status', __('attendance.saved'));
    }
}

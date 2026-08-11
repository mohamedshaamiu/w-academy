<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Squad;
use App\Models\Student;
use App\Services\EnrolmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SquadEnrolmentController extends Controller
{
    public function __construct(private readonly EnrolmentService $enrolment) {}

    public function store(Request $request, Squad $squad): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $this->enrolment->enrol(Student::findOrFail($validated['student_id']), $squad);

        return back()->with('status', __('admin.squad.student_enrolled'));
    }

    public function destroy(Squad $squad, Student $student): RedirectResponse
    {
        $this->enrolment->withdraw($student, $squad);

        return back()->with('status', __('admin.squad.student_withdrawn'));
    }
}

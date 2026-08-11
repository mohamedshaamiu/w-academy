<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianStudentController extends Controller
{
    public function index(Request $request): View
    {
        return view('guardian.children', [
            'students' => $request->user()->guardian->students()->with('activeEnrolment.squad')->get(),
        ]);
    }

    public function show(Request $request, Student $student): View
    {
        $this->authorize('view', $student);

        return view('guardian.child-detail', [
            'student' => $student->load(['activeEnrolment.squad', 'squads']),
            'attendances' => $student->attendances()
                ->with('trainingSession')
                ->when($request->filled('month'), fn ($q) => $q->whereMonth('marked_at', $request->integer('month')))
                ->latest('marked_at')
                ->paginate(20),
        ]);
    }
}

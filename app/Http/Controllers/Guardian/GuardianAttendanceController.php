<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianAttendanceController extends Controller
{
    public function index(Request $request, Student $student): View
    {
        $this->authorize('view', $student);

        return view('guardian.attendance', [
            'student' => $student,
            'attendances' => $student->attendances()
                ->with('trainingSession')
                ->when($request->filled('month'), fn ($q) => $q->whereMonth('marked_at', $request->integer('month')))
                ->latest('marked_at')
                ->paginate(20),
        ]);
    }
}

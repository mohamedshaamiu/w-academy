<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student;

        return view('student.attendance', [
            'attendances' => $student->attendances()
                ->with('trainingSession')
                ->when($request->filled('month'), fn ($q) => $q->whereMonth('marked_at', $request->integer('month')))
                ->latest('marked_at')
                ->paginate(20),
        ]);
    }
}

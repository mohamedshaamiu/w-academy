<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Services\AttendanceStatisticsService;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AttendanceStatisticsService $statistics) {}

    public function index(): View
    {
        $attendanceRate = $this->statistics->percentage(
            Attendance::whereBetween('marked_at', [now()->startOfWeek(), now()->endOfWeek()])->get()
        );

        return view('admin.dashboard', [
            'activeStudents' => Student::where('status', 'active')->count(),
            'studentsWithoutLogin' => Student::whereNull('user_id')->count(),
            'studentsWithoutSignature' => Student::whereDoesntHave('agreementSignatures', fn ($q) => $q->where('status', 'signed'))->count(),
            'activeSquads' => Squad::where('is_active', true)->count(),
            'sessionsToday' => TrainingSession::whereBetween('scheduled_start', [now()->startOfDay(), now()->endOfDay()])->count(),
            'attendanceRate' => $attendanceRate,
            'recentActivity' => Activity::with('causer', 'subject')->latest()->limit(10)->get(),
        ]);
    }
}

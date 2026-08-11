<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Squad;
use App\Models\Student;
use App\Services\AttendanceStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly AttendanceStatisticsService $statistics) {}

    public function attendance(Request $request): View|Response
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $squadId = $request->integer('squad') ?: null;

        $rows = $this->statistics->summariseByStudent(
            Attendance::query()
                ->whereBetween('marked_at', [$from, $to])
                ->when($squadId, fn ($q) => $q->whereHas('trainingSession', fn ($s) => $s->where('squad_id', $squadId)))
                ->with('student')
                ->get()
        );

        if ($request->boolean('export')) {
            return $this->exportCsv($rows);
        }

        return view('admin.reports.attendance', [
            'rows' => $rows,
            'squads' => Squad::where('is_active', true)->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * @param  Collection<int, array{student: Student, attended: int, total: int, excluded: int, percentage: int|null}>  $rows
     */
    private function exportCsv(Collection $rows): Response
    {
        $headers = [
            __('report.csv.index_number'),
            __('report.csv.full_name'),
            __('report.csv.present'),
            __('report.csv.total'),
            __('report.csv.percentage'),
        ];

        $lines = [implode(',', $headers)];

        foreach ($rows as $row) {
            $lines[] = implode(',', [
                $row['student']->index_number,
                '"'.str_replace('"', '""', $row['student']->full_name).'"',
                $row['attended'],
                $row['total'],
                $row['percentage'] !== null ? $row['percentage'].'%' : __('common.na'),
            ]);
        }

        $csv = "\xEF\xBB\xBF".implode("\r\n", $lines);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendance-report.csv"',
        ]);
    }
}

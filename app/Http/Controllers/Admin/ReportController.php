<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Squad;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function attendance(Request $request): View|Response
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $squadId = $request->integer('squad') ?: null;

        $rows = Attendance::query()
            ->whereBetween('marked_at', [$from, $to])
            ->when($squadId, fn ($q) => $q->whereHas('trainingSession', fn ($s) => $s->where('squad_id', $squadId)))
            ->with('student')
            ->get()
            ->groupBy('student_id')
            ->map(function ($attendances) {
                $student = $attendances->first()->student;
                $present = $attendances->whereIn('status', ['present', 'late'])->count();
                $total = $attendances->count();

                return [
                    'student' => $student,
                    'present' => $present,
                    'total' => $total,
                    'percentage' => $total > 0 ? (int) round($present / $total * 100) : 0,
                ];
            });

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

    private function exportCsv($rows): Response
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
                $row['present'],
                $row['total'],
                $row['percentage'].'%',
            ]);
        }

        $csv = "\xEF\xBB\xBF".implode("\r\n", $lines);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendance-report.csv"',
        ]);
    }
}

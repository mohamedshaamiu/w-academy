{{-- Expects: $attendances (paginated Attendance collection with trainingSession loaded) --}}
@php
    $currentMonth = request()->integer('month') ?: null;
@endphp

<div class="rounded-xl border border-navy-100 bg-white p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-semibold text-navy">{{ __('guardian.children.attendance_history') }}</h2>

        <form method="GET" class="flex items-center gap-2">
            <label for="month" class="text-sm text-navy-500">{{ __('guardian.attendance.month_filter') }}</label>
            <select id="month" name="month" class="rounded-md border-navy-200 text-sm" onchange="this.form.submit()">
                <option value="">{{ __('student.attendance.all_months') }}</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($currentMonth === $m)>{{ \App\Support\FormatsDates::monthName($m) }}</option>
                @endfor
            </select>
        </form>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.date') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('attendance.remark') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($attendances as $attendance)
                    <tr>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::dateTime($attendance->trainingSession->scheduled_start) }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$attendance->status->value" :label="$attendance->status->label()" /></td>
                        <td class="px-4 py-3 text-navy-500">{{ $attendance->remark ?? __('common.na') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attendances->links() }}</div>
</div>

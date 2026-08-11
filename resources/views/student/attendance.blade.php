<x-app-layout portal="student" :title="__('student.attendance.title')" :header="__('student.attendance.title')">
    <form method="GET" class="mb-4">
        <label for="month" class="sr-only">{{ __('student.attendance.month_filter') }}</label>
        <select id="month" name="month" onchange="this.form.submit()" class="rounded-md border-navy-200 text-sm">
            <option value="">{{ __('student.attendance.all_months') }}</option>
            @foreach (range(1, 12) as $m)
                <option value="{{ $m }}" @selected((int) request('month') === $m)>{{ __('common.month.'.$m) }}</option>
            @endforeach
        </select>
    </form>

    @php
        $pageItems = collect($attendances->items());
        $counts = collect(\App\Enums\AttendanceStatus::cases())->mapWithKeys(
            fn ($status) => [$status->value => $pageItems->where('status', $status)->count()]
        );
    @endphp

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach (\App\Enums\AttendanceStatus::cases() as $status)
            <div class="rounded-xl border border-navy-100 bg-white p-3 text-center shadow-sm">
                <p class="text-xl font-bold text-navy">{{ $counts[$status->value] }}</p>
                <p class="text-xs text-navy-400">{{ $status->label() }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
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
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::date($attendance->trainingSession?->scheduled_start) }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$attendance->status->value" :label="$attendance->status->label()" /></td>
                        <td class="px-4 py-3 text-navy-500">{{ $attendance->remark ?? __('common.none') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attendances->links() }}</div>
</x-app-layout>

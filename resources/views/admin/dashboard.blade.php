<x-app-layout portal="admin" :title="__('admin.dashboard.title')" :header="__('admin.dashboard.title')">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <p class="text-sm text-navy-400">{{ __('admin.dashboard.active_students') }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $activeStudents }}</p>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <p class="text-sm text-navy-400">{{ __('admin.dashboard.students_without_login') }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $studentsWithoutLogin }}</p>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <p class="text-sm text-navy-400">{{ __('admin.dashboard.students_without_agreement') }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $studentsWithoutSignature }}</p>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <p class="text-sm text-navy-400">{{ __('admin.dashboard.active_squads') }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $activeSquads }}</p>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <p class="text-sm text-navy-400">{{ __('admin.dashboard.sessions_today') }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $sessionsToday }}</p>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <p class="text-sm text-navy-400">{{ __('admin.dashboard.attendance_rate_week') }}</p>
            <p class="mt-2 text-3xl font-bold text-navy">
                {{ $attendanceRate !== null ? $attendanceRate.'%' : __('common.na') }}
            </p>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <h2 class="border-b border-navy-100 px-4 py-3 font-semibold text-navy">{{ __('admin.dashboard.recent_activity') }}</h2>

        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.causer') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.subject') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.action') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($recentActivity as $activity)
                    <tr>
                        <td class="px-4 py-3">{{ $activity->causer?->name ?? __('common.na') }}</td>
                        <td class="px-4 py-3">{{ class_basename($activity->subject_type) }}</td>
                        <td class="px-4 py-3">{{ $activity->description }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::dateTime($activity->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

<x-app-layout portal="student" :title="__('student.dashboard.title')" :header="__('student.dashboard.title')">
    <div class="max-w-xl rounded-xl border border-navy-100 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-4">
            <img src="{{ route('students.photo', $student) }}" alt="" class="h-16 w-16 shrink-0 rounded-full bg-navy-50 object-cover" onerror="this.style.display='none'">
            <div>
                <p class="text-lg font-semibold text-navy">{{ $student->full_name }}</p>
                <p class="text-sm text-navy-400">{{ $student->index_number }}</p>
                <div class="mt-1">
                    <x-status-badge :status="$student->status->value" :label="$student->status->label()" />
                </div>
            </div>
        </div>

        <dl class="mt-5 space-y-3 border-t border-navy-100 pt-4 text-sm text-navy-500">
            <div class="flex justify-between gap-4">
                <dt>{{ __('student.dashboard.squad') }}</dt>
                <dd class="text-end font-medium text-navy">{{ $squad?->translated('name') ?? __('student.dashboard.not_enrolled') }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt>{{ __('student.dashboard.next_session') }}</dt>
                <dd class="text-end font-medium text-navy">
                    @if ($next_session)
                        {{ \App\Support\FormatsDates::dateTime($next_session->scheduled_start) }}
                        <span class="block text-navy-400">
                            {{ $next_session->translated('venue') }} &middot; {{ $next_session->coach?->user?->name ?? __('common.na') }}
                        </span>
                    @else
                        {{ __('student.dashboard.no_upcoming_session') }}
                    @endif
                </dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt>{{ __('student.dashboard.attendance_this_month') }}</dt>
                <dd class="text-end font-medium text-navy">{{ $attendance_percentage !== null ? $attendance_percentage.'%' : __('common.na') }}</dd>
            </div>
        </dl>
    </div>
</x-app-layout>

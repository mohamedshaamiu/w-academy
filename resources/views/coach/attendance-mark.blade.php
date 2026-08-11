<x-app-layout portal="coach" :title="__('attendance.mark.title')" :header="__('attendance.mark.title')">
    @php
        $initialStatuses = [];

        foreach ($roster as $i => $student) {
            if ($student->status->value === 'suspended') {
                $initialStatuses[$i] = 'excused';
            } else {
                $initialStatuses[$i] = $existing->get($student->id)?->status->value;
            }
        }
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="font-semibold text-navy">{{ $session->squad?->translated('name') }}</p>
            <p class="text-sm text-navy-500">{{ \App\Support\FormatsDates::dateTime($session->scheduled_start) }}</p>
        </div>
        <a href="{{ route('coach.sessions.show', $session) }}" class="text-sm font-medium text-navy hover:underline">{{ __('common.actions.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('coach.sessions.attendance.store', $session) }}"
        x-data="{
            entries: @js($initialStatuses),
            count(status) {
                return Object.values(this.entries).filter((s) => s === status).length;
            },
        }"
        class="space-y-3"
    >
        @csrf

        @foreach ($roster as $i => $student)
            @php
                $isSuspended = $student->status->value === 'suspended';
                $existingRemark = $existing->get($student->id)?->remark;
            @endphp

            <div class="rounded-xl border {{ $isSuspended ? 'border-navy-100 bg-navy-50' : 'border-navy-100 bg-white' }} p-4">
                <div class="flex items-center gap-3">
                    <img src="{{ route('students.photo', $student) }}" alt="" class="h-12 w-12 shrink-0 rounded-full bg-navy-100 object-cover" onerror="this.style.display='none'">

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-navy">{{ $student->full_name }}</p>
                        <p class="text-xs text-navy-400">{{ $student->index_number }}</p>
                        @if ($isSuspended)
                            <p class="mt-0.5 text-xs font-medium text-red-600">{{ __('attendance.mark.suspended_notice') }}</p>
                        @endif
                    </div>
                </div>

                <input type="hidden" name="entries[{{ $i }}][student_id]" value="{{ $student->id }}">

                @if ($isSuspended)
                    <input type="hidden" name="entries[{{ $i }}][status]" value="excused">

                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                            <div class="flex min-h-[44px] items-center justify-center rounded-lg border py-2 text-center text-xs font-semibold {{ $status->value === 'excused' ? 'border-navy bg-navy text-white' : 'border-navy-100 text-navy-300' }}">
                                {{ $status->label() }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                            <label
                                class="flex min-h-[44px] cursor-pointer items-center justify-center rounded-lg border px-1 text-center text-xs font-semibold transition"
                                :class="entries[{{ $i }}] === '{{ $status->value }}' ? 'border-navy bg-navy text-white' : 'border-navy-100 text-navy-500 hover:border-navy-300'"
                            >
                                <input
                                    type="radio"
                                    name="entries[{{ $i }}][status]"
                                    value="{{ $status->value }}"
                                    class="sr-only"
                                    x-model="entries[{{ $i }}]"
                                    required
                                >
                                {{ $status->label() }}
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="mt-2">
                    <input
                        type="text"
                        name="entries[{{ $i }}][remark]"
                        value="{{ old("entries.$i.remark", $existingRemark) }}"
                        placeholder="{{ __('attendance.remark') }}"
                        maxlength="255"
                        class="w-full rounded-md border-navy-200 text-sm"
                    >
                </div>
            </div>
        @endforeach

        <div class="sticky bottom-0 -mx-4 border-t border-navy-100 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
                <dl class="flex flex-wrap gap-x-4 gap-y-1 text-xs font-medium text-navy-600">
                    <div class="flex items-center gap-1">
                        <span>{{ __('attendance.status.present') }}</span>
                        <span class="font-semibold text-navy" x-text="count('present')"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span>{{ __('attendance.status.absent') }}</span>
                        <span class="font-semibold text-navy" x-text="count('absent')"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span>{{ __('attendance.status.late') }}</span>
                        <span class="font-semibold text-navy" x-text="count('late')"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span>{{ __('attendance.status.excused') }}</span>
                        <span class="font-semibold text-navy" x-text="count('excused')"></span>
                    </div>
                </dl>

                <button type="submit" class="inline-flex min-h-[44px] items-center rounded-lg bg-gold px-6 text-sm font-semibold text-navy-900 hover:bg-gold-400">
                    {{ __('attendance.mark.save') }}
                </button>
            </div>
        </div>
    </form>
</x-app-layout>

<x-app-layout portal="guardian" :title="__('guardian.dashboard.title')" :header="__('guardian.dashboard.title')">
    @if ($cards->isEmpty())
        <p class="text-navy-400">{{ __('guardian.dashboard.no_children') }}</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($cards as $card)
                <a href="{{ route('guardian.children.show', $card['student']) }}" class="block rounded-xl border border-navy-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <x-student-photo :student="$card['student']" class="h-12 w-12" />
                        <div>
                            <p class="font-semibold text-navy">{{ $card['student']->full_name }}</p>
                            <p class="text-xs text-navy-400">{{ $card['student']->index_number }}</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <x-status-badge :status="$card['student']->status->value" :label="$card['student']->status->label()" />
                    </div>

                    <dl class="mt-3 space-y-1 text-sm text-navy-500">
                        <div class="flex justify-between">
                            <dt>{{ __('student.dashboard.squad') }}</dt>
                            <dd class="font-medium text-navy">{{ $card['squad']?->translated('name') ?? __('common.na') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>{{ __('student.dashboard.next_session') }}</dt>
                            <dd class="font-medium text-navy">
                                {{ $card['next_session'] ? \App\Support\FormatsDates::dateTime($card['next_session']->scheduled_start) : __('student.dashboard.no_upcoming_session') }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>{{ __('student.dashboard.attendance_this_month') }}</dt>
                            <dd class="font-medium text-navy">{{ $card['attendance_percentage'] !== null ? $card['attendance_percentage'].'%' : __('common.na') }}</dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>

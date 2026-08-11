<x-app-layout portal="coach" :title="__('coach.dashboard.title')" :header="__('coach.dashboard.title')">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('coach.dashboard.today_sessions') }}</h2>

                @if ($todaySessions->isEmpty())
                    <p class="mt-3 text-sm text-navy-400">{{ __('coach.dashboard.no_sessions_today') }}</p>
                @else
                    <ul class="mt-3 divide-y divide-navy-100">
                        @foreach ($todaySessions as $session)
                            <li>
                                <a href="{{ route('coach.sessions.show', $session) }}" class="flex min-h-[44px] items-center justify-between gap-3 py-3 hover:bg-navy-50">
                                    <div>
                                        <p class="font-medium text-navy">{{ $session->squad?->translated('name') }}</p>
                                        <p class="text-sm text-navy-500">
                                            {{ \App\Support\FormatsDates::time($session->scheduled_start) }} – {{ \App\Support\FormatsDates::time($session->scheduled_end) }}
                                        </p>
                                    </div>
                                    <x-status-badge :status="$session->status->value" :label="$session->status->label()" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6 text-center">
                <p class="text-3xl font-bold text-navy">{{ $totalStudents }}</p>
                <p class="mt-1 text-sm text-navy-500">{{ __('coach.dashboard.total_students') }}</p>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('coach.dashboard.my_squads') }}</h2>

                @if ($squads->isEmpty())
                    <p class="mt-3 text-sm text-navy-400">{{ __('common.none') }}</p>
                @else
                    <ul class="mt-3 space-y-1">
                        @foreach ($squads as $squad)
                            <li>
                                <a href="{{ route('coach.squads.show', $squad) }}" class="flex min-h-[44px] items-center justify-between rounded-lg px-2 py-2 text-sm hover:bg-navy-50">
                                    <span class="font-medium text-navy">{{ $squad->translated('name') }}</span>
                                    <span class="text-navy-400">{{ $squad->active_students_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

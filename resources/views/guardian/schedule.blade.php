<x-app-layout portal="guardian" :title="__('guardian.schedule.title')" :header="__('guardian.schedule.title')">
    @if ($sessionsByDate->isEmpty())
        <p class="text-navy-400">{{ __('guardian.schedule.no_sessions') }}</p>
    @else
        <div class="space-y-6">
            @foreach ($sessionsByDate as $date => $sessions)
                <div>
                    <h2 class="mb-2 text-sm font-semibold text-navy-500">
                        {{ \App\Support\FormatsDates::longDate(\Carbon\Carbon::parse($date)) }}
                    </h2>

                    <div class="space-y-3">
                        @foreach ($sessions as $session)
                            <div class="rounded-xl border border-navy-100 bg-white p-4 sm:flex sm:items-center sm:justify-between sm:gap-4">
                                <div>
                                    <p class="font-semibold text-navy">{{ $session->squad?->translated('name') ?? __('common.na') }}</p>
                                    <p class="text-sm text-navy-500">
                                        {{ \App\Support\FormatsDates::time($session->scheduled_start) }} – {{ \App\Support\FormatsDates::time($session->scheduled_end) }}
                                        · {{ $session->translated('venue') }}
                                    </p>
                                </div>

                                <p class="mt-2 text-sm text-navy-400 sm:mt-0">
                                    {{ __('session.fields.coach') }}: {{ $session->coach?->user?->name ?? __('common.na') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>

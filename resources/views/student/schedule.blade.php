<x-app-layout portal="student" :title="__('student.schedule.title')" :header="__('student.schedule.title')">
    @if ($sessionsByDate->isEmpty())
        <p class="text-navy-400">{{ __('student.schedule.no_sessions') }}</p>
    @else
        <div class="space-y-6">
            @foreach ($sessionsByDate as $date => $sessions)
                <div>
                    <h2 class="mb-2 text-sm font-semibold text-navy">
                        {{ \App\Support\FormatsDates::longDate(\Carbon\Carbon::parse($date)) }}
                    </h2>

                    <div class="space-y-2">
                        @foreach ($sessions as $session)
                            <div class="rounded-xl border border-navy-100 bg-white p-4 shadow-sm">
                                <p class="font-medium text-navy">
                                    {{ \App\Support\FormatsDates::time($session->scheduled_start) }}&ndash;{{ \App\Support\FormatsDates::time($session->scheduled_end) }}
                                </p>
                                <dl class="mt-1 space-y-0.5 text-sm text-navy-500">
                                    <div class="flex gap-2">
                                        <dt class="text-navy-400">{{ __('common.labels.venue') }}:</dt>
                                        <dd>{{ $session->translated('venue') }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="text-navy-400">{{ __('session.fields.coach') }}:</dt>
                                        <dd>{{ $session->coach?->user?->name ?? __('common.na') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>

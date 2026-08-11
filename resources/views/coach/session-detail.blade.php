<x-app-layout portal="coach" :title="$session->squad?->translated('name')" :header="$session->squad?->translated('name')">
    <div class="mb-4">
        <a href="{{ route('coach.sessions.index') }}" class="text-sm font-medium text-navy hover:underline">{{ __('common.actions.back') }}</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <x-status-badge :status="$session->status->value" :label="$session->status->label()" />

                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-navy-400">{{ __('session.fields.squad') }}</dt>
                        <dd class="font-medium text-navy">{{ $session->squad?->translated('name') }}</dd>
                    </div>
                    <div>
                        <dt class="text-navy-400">{{ __('session.fields.venue') }}</dt>
                        <dd class="font-medium text-navy">{{ $session->translated('venue') ?? __('common.na') }}</dd>
                    </div>
                    <div>
                        <dt class="text-navy-400">{{ __('session.fields.scheduled_start') }}</dt>
                        <dd class="font-medium text-navy">{{ \App\Support\FormatsDates::dateTime($session->scheduled_start) }}</dd>
                    </div>
                    <div>
                        <dt class="text-navy-400">{{ __('session.fields.scheduled_end') }}</dt>
                        <dd class="font-medium text-navy">{{ \App\Support\FormatsDates::dateTime($session->scheduled_end) }}</dd>
                    </div>
                    @if ($session->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-navy-400">{{ __('session.fields.notes') }}</dt>
                            <dd class="font-medium text-navy">{{ $session->notes }}</dd>
                        </div>
                    @endif
                    @if ($session->status === \App\Enums\SessionStatus::Cancelled)
                        <div class="sm:col-span-2">
                            <dt class="text-navy-400">{{ __('session.fields.cancellation_reason') }}</dt>
                            <dd class="font-medium text-navy">{{ $session->cancellation_reason }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-navy-100 pt-4">
                    @if ($session->status === \App\Enums\SessionStatus::Planned)
                        <form method="POST" action="{{ route('coach.sessions.start', $session) }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-[44px] items-center rounded-lg bg-navy px-5 text-sm font-semibold text-white hover:bg-navy-600">
                                {{ __('common.actions.start') }}
                            </button>
                        </form>
                    @elseif ($session->status === \App\Enums\SessionStatus::InProgress)
                        <form method="POST" action="{{ route('coach.sessions.complete', $session) }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-[44px] items-center rounded-lg bg-navy px-5 text-sm font-semibold text-white hover:bg-navy-600">
                                {{ __('common.actions.complete') }}
                            </button>
                        </form>
                        <a href="{{ route('coach.sessions.attendance.edit', $session) }}" class="inline-flex min-h-[44px] items-center rounded-lg bg-gold px-5 text-sm font-semibold text-navy-900 hover:bg-gold-400">
                            {{ __('attendance.mark.title') }}
                        </a>
                    @elseif ($session->status === \App\Enums\SessionStatus::Completed)
                        @if ($session->is_read_only_for_coach)
                            <p class="text-sm text-navy-400">{{ __('session.read_only_notice') }}</p>
                        @else
                            <a href="{{ route('coach.sessions.attendance.edit', $session) }}" class="inline-flex min-h-[44px] items-center rounded-lg bg-gold px-5 text-sm font-semibold text-navy-900 hover:bg-gold-400">
                                {{ __('attendance.mark.title') }}
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('attendance.summary_heading') }}</h2>

                @if ($session->attendances->isEmpty())
                    <p class="mt-3 text-sm text-navy-400">{{ __('common.none') }}</p>
                @else
                    <ul class="mt-3 divide-y divide-navy-100">
                        @foreach ($session->attendances as $attendance)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <span class="font-medium text-navy">{{ $attendance->student->full_name }}</span>
                                <x-status-badge :status="$attendance->status->value" :label="$attendance->status->label()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

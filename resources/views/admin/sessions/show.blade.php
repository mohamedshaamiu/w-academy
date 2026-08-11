<x-app-layout portal="admin" :title="__('admin.session.show.title')" :header="$session->squad->translated('name')">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <x-status-badge :status="$session->status->value" :label="$session->status->label()" />

        <div class="ms-auto flex flex-wrap gap-2">
            <a href="{{ route('admin.sessions.edit', $session) }}" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
                {{ __('common.actions.edit') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.session.show.title') }}</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-navy-400">{{ __('session.fields.squad') }}</dt><dd>{{ $session->squad->translated('name') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('session.fields.coach') }}</dt><dd>{{ $session->coach?->user?->name ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('session.fields.scheduled_start') }}</dt><dd>{{ \App\Support\FormatsDates::dateTime($session->scheduled_start) }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('session.fields.scheduled_end') }}</dt><dd>{{ \App\Support\FormatsDates::dateTime($session->scheduled_end) }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('session.fields.venue') }}</dt><dd>{{ $session->translated('venue') ?: __('common.na') }}</dd></div>
                    @if ($session->notes)
                        <div class="col-span-2"><dt class="text-navy-400">{{ __('session.fields.notes') }}</dt><dd>{{ $session->notes }}</dd></div>
                    @endif
                    @if ($session->status === \App\Enums\SessionStatus::Cancelled && $session->cancellation_reason)
                        <div class="col-span-2"><dt class="text-navy-400">{{ __('session.fields.cancellation_reason') }}</dt><dd>{{ $session->cancellation_reason }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.session.show.attendance_heading') }}</h2>
                <ul class="mt-3 divide-y divide-navy-100 text-sm">
                    @forelse ($session->attendances as $attendance)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $attendance->student->full_name }}</span>
                            <x-status-badge :status="$attendance->status->value" :label="$attendance->status->label()" />
                        </li>
                    @empty
                        <li class="py-2 text-navy-400">{{ __('common.none') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            @if (! in_array($session->status, [\App\Enums\SessionStatus::Cancelled, \App\Enums\SessionStatus::Completed], true))
                <div class="rounded-xl border border-navy-100 bg-white p-6">
                    <h2 class="font-semibold text-navy">{{ __('admin.session.show.cancel_heading') }}</h2>
                    <form
                        method="POST"
                        action="{{ route('admin.sessions.destroy', $session) }}"
                        class="mt-4 space-y-3"
                        onsubmit="return confirm('{{ __('common.confirm_prompt') }}')"
                    >
                        @csrf @method('DELETE')
                        <div>
                            <x-input-label for="cancellation_reason" :value="__('session.fields.cancellation_reason')" />
                            <x-text-input id="cancellation_reason" name="cancellation_reason" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('cancellation_reason')" class="mt-2" />
                        </div>
                        <button type="submit" class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                            {{ __('common.actions.cancel_session') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

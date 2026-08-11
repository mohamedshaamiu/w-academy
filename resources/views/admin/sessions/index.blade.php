<x-app-layout portal="admin" :title="__('admin.session.index.title')" :header="__('admin.session.index.title')">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <select name="squad" class="rounded-md border-navy-200 text-sm">
                <option value="">{{ __('admin.session.index.filter_squad') }}</option>
                @foreach ($squads as $squad)
                    <option value="{{ $squad->id }}" @selected((string) request('squad') === (string) $squad->id)>{{ $squad->translated('name') }}</option>
                @endforeach
            </select>

            <div>
                <label class="block text-xs text-navy-400">{{ __('admin.session.index.filter_from') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-md border-navy-200 text-sm">
            </div>

            <div>
                <label class="block text-xs text-navy-400">{{ __('admin.session.index.filter_to') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-md border-navy-200 text-sm">
            </div>

            <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-medium text-white hover:bg-navy-600">
                {{ __('common.actions.filter') }}
            </button>
        </form>

        <div class="flex flex-wrap gap-2">
            <button type="button" x-data @click="$dispatch('open-modal', 'generate-sessions')" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
                {{ __('admin.session.generate_button') }}
            </button>

            <a href="{{ route('admin.sessions.create') }}" class="shrink-0 rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
                + {{ __('common.actions.create') }}
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('session.fields.squad') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('session.fields.coach') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('session.fields.scheduled_start') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('session.fields.scheduled_end') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('session.fields.venue') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($sessions as $session)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.sessions.show', $session) }}'">
                        <td class="px-4 py-3 font-medium text-navy">{{ $session->squad->translated('name') }}</td>
                        <td class="px-4 py-3">{{ $session->coach?->user?->name ?? __('common.na') }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::dateTime($session->scheduled_start) }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::time($session->scheduled_end) }}</td>
                        <td class="px-4 py-3">{{ $session->translated('venue') ?: __('common.na') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$session->status->value" :label="$session->status->label()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $sessions->links() }}</div>

    <x-modal name="generate-sessions">
        @include('admin.sessions.partials.generate-modal')
    </x-modal>
</x-app-layout>

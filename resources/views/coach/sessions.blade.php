<x-app-layout portal="coach" :title="__('coach.sessions.title')" :header="__('coach.sessions.title')">
    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.date_time') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('session.fields.squad') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($sessions as $session)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('coach.sessions.show', $session) }}'">
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::dateTime($session->scheduled_start) }}</td>
                        <td class="px-4 py-3">{{ $session->squad?->translated('name') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$session->status->value" :label="$session->status->label()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $sessions->links() }}</div>
</x-app-layout>

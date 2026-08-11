<x-app-layout portal="admin" :title="__('admin.audit.title')" :header="__('admin.audit.title')">
    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.causer') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.subject') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.action') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.audit.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($activities as $activity)
                    <tr>
                        <td class="px-4 py-3">{{ $activity->causer?->name ?? __('common.na') }}</td>
                        <td class="px-4 py-3">{{ class_basename($activity->subject_type) }}</td>
                        <td class="px-4 py-3">{{ $activity->description }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::dateTime($activity->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>
</x-app-layout>

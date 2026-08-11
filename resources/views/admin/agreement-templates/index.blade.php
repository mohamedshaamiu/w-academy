<x-app-layout portal="admin" :title="__('admin.agreement.index.title')" :header="__('admin.agreement.index.title')">
    <div class="mb-4 flex items-center justify-end">
        <a href="{{ route('admin.agreement-templates.create') }}" class="shrink-0 rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
            + {{ __('common.actions.create') }}
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.fields.version') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.fields.title') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.fields.effective_from') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($templates as $template)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.agreement-templates.show', $template) }}'">
                        <td class="px-4 py-3 font-mono">{{ $template->version }}</td>
                        <td class="px-4 py-3">{{ $template->translated('title') }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::date($template->effective_from) }}</td>
                        <td class="px-4 py-3">
                            @if ($template->is_current)
                                <span class="inline-flex items-center rounded-full bg-gold-100 px-2.5 py-0.5 text-xs font-semibold text-navy-900">
                                    {{ __('admin.agreement.current_badge') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $templates->links() }}</div>
</x-app-layout>

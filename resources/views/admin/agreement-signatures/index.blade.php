<x-app-layout portal="admin" :title="__('admin.agreement.signatures.title')" :header="__('admin.agreement.signatures.title')">
    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.signatures.table.student') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.signatures.table.guardian') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.fields.version') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.signatures.table.signed_at') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.signatures.table.language') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.agreement.signatures.snapshot_view') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($signatures as $signature)
                    <tr>
                        <td class="px-4 py-3">{{ $signature->student?->full_name }} <span class="font-mono text-navy-400">({{ $signature->student?->index_number }})</span></td>
                        <td class="px-4 py-3">{{ $signature->guardian?->user?->name }}</td>
                        <td class="px-4 py-3 font-mono">{{ $signature->agreementTemplate?->version }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::dateTime($signature->signed_at) }}</td>
                        <td class="px-4 py-3">{{ $signature->signed_locale?->label() }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$signature->status->value" :label="$signature->status->label()" /></td>
                        <td class="px-4 py-3">
                            <details>
                                <summary class="cursor-pointer text-navy underline">{{ __('admin.agreement.signatures.snapshot_view') }}</summary>
                                <pre class="mt-2 max-w-md whitespace-pre-wrap rounded-md bg-navy-50 p-3 text-xs text-navy-700">{{ $signature->template_snapshot }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $signatures->links() }}</div>
</x-app-layout>

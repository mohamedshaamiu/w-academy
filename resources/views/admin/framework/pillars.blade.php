<x-app-layout portal="admin" :title="__('admin.framework.pillars.title')" :header="__('admin.framework.pillars.title')">
    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.framework.pillars.fields.code') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.framework.pillars.fields.name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.framework.pillars.fields.icon') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.framework.pillars.fields.sort_order') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.active') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($pillars as $pillar)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.framework.pillars.edit', $pillar) }}'">
                        <td class="px-4 py-3 font-mono">{{ $pillar->code }}</td>
                        <td class="px-4 py-3">{{ $pillar->translated('name') }}</td>
                        <td class="px-4 py-3">{{ $pillar->icon }}</td>
                        <td class="px-4 py-3">{{ $pillar->sort_order }}</td>
                        <td class="px-4 py-3">{{ $pillar->is_active ? __('common.yes') : __('common.no') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

<x-app-layout portal="admin" :title="__('admin.framework.strike_levels.title')" :header="__('admin.framework.strike_levels.title')">
    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.level') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('admin.framework.strike_levels.fields.label') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.type') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.action') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.active') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($levels as $level)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.framework.strike-levels.edit', $level) }}'">
                        <td class="px-4 py-3 font-mono">{{ $level->level }}</td>
                        <td class="px-4 py-3">{{ $level->translated('label') }}</td>
                        <td class="px-4 py-3">{{ $level->translated('type') }}</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($level->translated('action'), 60) }}</td>
                        <td class="px-4 py-3">{{ $level->is_active ? __('common.yes') : __('common.no') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

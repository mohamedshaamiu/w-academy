<x-app-layout portal="admin" :title="__('admin.squad.index.title')" :header="__('admin.squad.index.title')">
    <div class="mb-4 flex items-center justify-end">
        <a href="{{ route('admin.squads.create') }}" class="shrink-0 rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
            + {{ __('common.actions.create') }}
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('squad.fields.name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('squad.fields.age_group') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('squad.fields.head_coach') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('squad.fields.venue') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('squad.fields.active_students') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($squads as $squad)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.squads.show', $squad) }}'">
                        <td class="px-4 py-3 font-medium text-navy">{{ $squad->translated('name') }}</td>
                        <td class="px-4 py-3">{{ $squad->age_group }}</td>
                        <td class="px-4 py-3">{{ $squad->headCoach?->user?->name ?? __('common.na') }}</td>
                        <td class="px-4 py-3">{{ $squad->translated('venue') ?: __('common.na') }}</td>
                        <td class="px-4 py-3">{{ $squad->active_students_count }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge
                                :status="$squad->is_active ? 'active' : 'inactive'"
                                :label="$squad->is_active ? __('squad.fields.is_active') : __('common.no')"
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $squads->links() }}</div>
</x-app-layout>

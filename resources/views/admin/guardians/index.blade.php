<x-app-layout portal="admin" :title="__('admin.guardian.index.title')" :header="__('admin.guardian.index.title')">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.guardian.index.search_placeholder') }}" class="rounded-md border-navy-200 text-sm">

            <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-medium text-white hover:bg-navy-600">
                {{ __('common.actions.filter') }}
            </button>
        </form>

        <a href="{{ route('admin.guardians.create') }}" class="shrink-0 rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
            + {{ __('common.actions.create') }}
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.phone') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.email') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.address') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($guardians as $guardian)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.guardians.show', $guardian) }}'">
                        <td class="px-4 py-3 font-medium text-navy">{{ $guardian->user->name }}</td>
                        <td class="px-4 py-3">{{ $guardian->user->phone }}</td>
                        <td class="px-4 py-3">{{ $guardian->user->email ?? __('common.na') }}</td>
                        <td class="px-4 py-3">{{ $guardian->address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $guardians->links() }}</div>
</x-app-layout>

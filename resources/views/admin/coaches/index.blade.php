<x-app-layout portal="admin" :title="__('admin.coach.index.title')" :header="__('admin.coach.index.title')">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.coach.index.search_placeholder') }}" class="rounded-md border-navy-200 text-sm">

            <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-medium text-white hover:bg-navy-600">
                {{ __('common.actions.filter') }}
            </button>
        </form>

        <a href="{{ route('admin.coaches.create') }}" class="shrink-0 rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
            + {{ __('common.actions.create') }}
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('coach.fields.coach_no') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.phone') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('coach.fields.specialisation') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('coach.fields.joined_on') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($coaches as $coach)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.coaches.show', $coach) }}'">
                        <td class="px-4 py-3 font-mono">{{ $coach->coach_no }}</td>
                        <td class="px-4 py-3 font-medium text-navy">{{ $coach->user->name }}</td>
                        <td class="px-4 py-3">{{ $coach->user->phone }}</td>
                        <td class="px-4 py-3">{{ $coach->specialisation ?? __('common.na') }}</td>
                        <td class="px-4 py-3">{{ \App\Support\FormatsDates::date($coach->joined_on) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $coaches->links() }}</div>
</x-app-layout>

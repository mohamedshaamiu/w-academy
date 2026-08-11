<x-app-layout portal="admin" :title="__('report.attendance.title')" :header="__('report.attendance.title')">
    <form method="GET" action="{{ route('admin.reports.attendance') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <div>
            <x-input-label for="from" :value="__('report.attendance.from')" />
            <input id="from" type="date" name="from" value="{{ request('from', $from->toDateString()) }}" class="mt-1 block rounded-md border-navy-200 text-sm">
        </div>

        <div>
            <x-input-label for="to" :value="__('report.attendance.to')" />
            <input id="to" type="date" name="to" value="{{ request('to', $to->toDateString()) }}" class="mt-1 block rounded-md border-navy-200 text-sm">
        </div>

        <div>
            <x-input-label for="squad" :value="__('report.attendance.squad')" />
            <select id="squad" name="squad" class="mt-1 block rounded-md border-navy-200 text-sm">
                <option value="">—</option>
                @foreach ($squads as $squad)
                    <option value="{{ $squad->id }}" @selected((string) request('squad') === (string) $squad->id)>{{ $squad->translated('name') }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-medium text-white hover:bg-navy-600">
            {{ __('common.actions.filter') }}
        </button>

        <button type="submit" name="export" value="1" class="rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
            {{ __('report.attendance.export') }}
        </button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('report.csv.index_number') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('report.csv.full_name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('report.csv.present') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('report.csv.total') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('report.csv.percentage') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 font-mono">{{ $row['student']->index_number }}</td>
                        <td class="px-4 py-3">{{ $row['student']->full_name }}</td>
                        <td class="px-4 py-3">{{ $row['present'] }}</td>
                        <td class="px-4 py-3">{{ $row['total'] }}</td>
                        <td class="px-4 py-3">{{ $row['percentage'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

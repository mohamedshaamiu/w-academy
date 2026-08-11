<x-app-layout portal="coach" :title="$squad->translated('name')" :header="$squad->translated('name')">
    <div class="mb-4 flex flex-wrap items-center gap-3 text-sm text-navy-500">
        <span>{{ $squad->age_group }}</span>
        <span>&middot;</span>
        <span>{{ $squad->translated('venue') ?? __('common.na') }}</span>
        <a href="{{ route('coach.squads.index') }}" class="ms-auto font-medium text-navy hover:underline">{{ __('common.actions.back') }}</a>
    </div>

    <h2 class="mb-2 font-semibold text-navy">{{ __('coach.squads.roster') }}</h2>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3"></th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.fields.index_number') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.fields.full_name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.fields.age') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($roster as $student)
                    <tr>
                        <td class="px-4 py-3">
                            <img src="{{ route('students.photo', $student) }}" alt="" class="h-10 w-10 rounded-full bg-navy-50 object-cover" onerror="this.style.display='none'">
                        </td>
                        <td class="px-4 py-3 font-mono">{{ $student->index_number }}</td>
                        <td class="px-4 py-3">{{ $student->full_name }}</td>
                        <td class="px-4 py-3">{{ $student->age }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$student->status->value" :label="$student->status->label()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

<x-app-layout portal="guardian" :title="__('guardian.children.title')" :header="__('guardian.children.title')">
    @if ($students->isEmpty())
        <p class="text-navy-400">{{ __('guardian.dashboard.no_children') }}</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($students as $student)
                <a href="{{ route('guardian.children.show', $student) }}" class="block rounded-xl border border-navy-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <img src="{{ route('students.photo', $student) }}" alt="" class="h-12 w-12 rounded-full bg-navy-50 object-cover" onerror="this.style.display='none'">
                        <div>
                            <p class="font-semibold text-navy">{{ $student->full_name }}</p>
                            <p class="text-xs text-navy-400">{{ $student->index_number }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-2">
                        <x-status-badge :status="$student->status->value" :label="$student->status->label()" />
                        <span class="text-sm font-medium text-navy">{{ $student->activeEnrolment?->squad?->translated('name') ?? __('common.na') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>

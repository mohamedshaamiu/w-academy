<x-app-layout portal="guardian" :title="__('guardian.attendance.title')" :header="$student->full_name">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-navy-400">{{ __('guardian.attendance.title') }}</p>
        <a href="{{ route('guardian.children.show', $student) }}" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
            {{ __('common.actions.back') }}
        </a>
    </div>

    @include('guardian.partials.attendance-table', ['attendances' => $attendances])
</x-app-layout>

<x-app-layout portal="coach" :title="__('coach.squads.title')" :header="__('coach.squads.title')">
    @if ($squads->isEmpty())
        <p class="text-navy-400">{{ __('common.none') }}</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($squads as $squad)
                <a href="{{ route('coach.squads.show', $squad) }}" class="block rounded-xl border border-navy-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <p class="font-semibold text-navy">{{ $squad->translated('name') }}</p>
                    <p class="text-sm text-navy-400">{{ $squad->age_group }}</p>

                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-navy-500">{{ __('squad.fields.active_students') }}</span>
                        <span class="font-semibold text-navy">{{ $squad->active_students_count }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>

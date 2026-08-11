<x-app-layout portal="admin" :title="$squad->translated('name')" :header="$squad->translated('name')">
    @php
        $weekdayLabel = fn (int $isoDay) => __('common.weekday.'.($isoDay === 7 ? 0 : $isoDay));
    @endphp

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <x-status-badge
            :status="$squad->is_active ? 'active' : 'inactive'"
            :label="$squad->is_active ? __('squad.fields.is_active') : __('common.no')"
        />

        <div class="ms-auto flex flex-wrap gap-2">
            <a href="{{ route('admin.squads.edit', $squad) }}" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
                {{ __('common.actions.edit') }}
            </a>

            <form method="POST" action="{{ route('admin.squads.destroy', $squad) }}" onsubmit="return confirm('{{ __('common.confirm_prompt') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    {{ __('common.actions.delete') }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.squad.show.title') }}</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-navy-400">{{ __('squad.fields.age_group') }}</dt><dd>{{ $squad->age_group }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('squad.fields.head_coach') }}</dt><dd>{{ $squad->headCoach?->user?->name ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('squad.fields.venue') }}</dt><dd>{{ $squad->translated('venue') ?: __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('squad.fields.capacity') }}</dt><dd>{{ $squad->capacity ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('squad.fields.default_start_time') }}</dt><dd>{{ $squad->default_start_time ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('squad.fields.default_end_time') }}</dt><dd>{{ $squad->default_end_time ?? __('common.na') }}</dd></div>
                    <div class="col-span-2">
                        <dt class="text-navy-400">{{ __('squad.fields.training_days') }}</dt>
                        <dd>
                            @forelse (collect($squad->training_days ?? [])->sort() as $isoDay)
                                <span class="me-2 inline-block">{{ $weekdayLabel($isoDay) }}</span>
                            @empty
                                {{ __('common.none') }}
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.squad.show.roster_heading') }}</h2>
                <table class="mt-3 min-w-full divide-y divide-navy-100 text-start text-sm">
                    <thead>
                        <tr>
                            <th class="py-2 text-start font-semibold text-navy">{{ __('student.fields.index_number') }}</th>
                            <th class="py-2 text-start font-semibold text-navy">{{ __('student.fields.full_name') }}</th>
                            <th class="py-2 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-100">
                        @forelse ($roster as $student)
                            <tr>
                                <td class="py-2 font-mono">
                                    <a href="{{ route('admin.students.show', $student) }}" class="text-navy hover:underline">{{ $student->index_number }}</a>
                                </td>
                                <td class="py-2">{{ $student->full_name }}</td>
                                <td class="py-2"><x-status-badge :status="$student->status->value" :label="$student->status->label()" /></td>
                                <td class="py-2 text-end">
                                    <form method="POST" action="{{ route('admin.squads.enrol.destroy', [$squad, $student]) }}" onsubmit="return confirm('{{ __('common.confirm_prompt') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 underline">{{ __('common.actions.remove') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.squad.show.enrol_heading') }}</h2>
                <p class="mt-2 text-sm text-navy-500">{{ __('admin.squad.show.enrol_hint') }}</p>

                <form method="POST" action="{{ route('admin.squads.enrol.store', $squad) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <x-input-label for="student_id" :value="__('admin.squad.show.enrol_student_id_label')" />
                        <x-text-input id="student_id" type="number" min="1" name="student_id" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('common.actions.add') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

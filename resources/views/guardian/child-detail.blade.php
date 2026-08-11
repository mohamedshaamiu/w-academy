<x-app-layout portal="guardian" :title="$student->full_name" :header="$student->full_name">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('guardian.children.index') }}" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
            {{ __('common.actions.back') }}
        </a>
        <x-status-badge :status="$student->status->value" :label="$student->status->label()" />
    </div>

    <div class="max-w-4xl space-y-6">
        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('guardian.children.index_title') }}</h2>

            <div class="mt-4 flex items-center gap-4">
                <x-student-photo :student="$student" class="h-16 w-16" />
                <div>
                    <p class="text-lg font-semibold text-navy">{{ $student->full_name }}</p>
                    <p class="text-sm text-navy-400">{{ $student->index_number }}</p>
                </div>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-navy-400">{{ __('student.fields.age') }}</dt>
                    <dd>{{ $student->age }}</dd>
                </div>
                <div>
                    <dt class="text-navy-400">{{ __('student.fields.gender') }}</dt>
                    <dd>{{ $student->gender === 'male' ? __('student.fields.gender_male') : __('student.fields.gender_female') }}</dd>
                </div>
                <div>
                    <dt class="text-navy-400">{{ __('student.fields.school_name') }}</dt>
                    <dd>{{ $student->school_name ?? __('common.na') }}</dd>
                </div>
                <div>
                    <dt class="text-navy-400">{{ __('student.fields.class_level') }}</dt>
                    <dd>{{ $student->class_level ?? __('common.na') }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-navy-400">{{ __('student.dashboard.squad') }}</dt>
                    <dd class="font-medium text-navy">{{ $student->activeEnrolment?->squad?->translated('name') ?? __('student.dashboard.not_enrolled') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('guardian.children.enrolment_history') }}</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.dashboard.squad') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('guardian.children.enrolled_on') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('guardian.children.left_on') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-100">
                        @forelse ($student->squads as $squad)
                            <tr>
                                <td class="px-4 py-3 font-medium text-navy">{{ $squad->translated('name') }}</td>
                                <td class="px-4 py-3">{{ \App\Support\FormatsDates::date($squad->pivot->enrolled_on) }}</td>
                                <td class="px-4 py-3">{{ $squad->pivot->left_on ? \App\Support\FormatsDates::date($squad->pivot->left_on) : __('guardian.children.current') }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge
                                        :status="$squad->pivot->is_active ? 'active' : 'inactive'"
                                        :label="$squad->pivot->is_active ? __('student.status.active') : __('student.status.inactive')"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('guardian.attendance', $student) }}" class="text-sm font-medium text-navy hover:underline">
                {{ __('common.actions.view') }} — {{ __('guardian.attendance.title') }}
            </a>
        </div>

        @include('guardian.partials.attendance-table', ['attendances' => $attendances])
    </div>
</x-app-layout>

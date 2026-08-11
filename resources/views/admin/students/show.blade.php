<x-app-layout portal="admin" :title="$student->full_name" :header="$student->full_name">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <x-status-badge :status="$student->status->value" :label="$student->status->label()" />

        <form method="POST" action="{{ route('admin.students.status', $student) }}" class="flex items-center gap-2">
            @csrf
            <select name="status" class="rounded-md border-navy-200 text-sm" onchange="this.form.submit()">
                @foreach (\App\Enums\StudentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($student->status === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <a href="{{ route('admin.students.edit', $student) }}" class="ms-auto rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
            {{ __('common.actions.edit') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.student.show.title') }}</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-navy-400">{{ __('student.fields.index_number') }}</dt><dd class="font-mono">{{ $student->index_number }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('student.fields.age') }}</dt><dd>{{ $student->age }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('student.fields.date_of_birth') }}</dt><dd>{{ \App\Support\FormatsDates::date($student->date_of_birth) }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('student.fields.school_name') }}</dt><dd>{{ $student->school_name ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('student.fields.class_level') }}</dt><dd>{{ $student->class_level ?? __('common.na') }}</dd></div>
                    <div class="col-span-2"><dt class="text-navy-400">{{ __('student.fields.address') }}</dt><dd>{{ $student->address }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.student.show.guardians_heading') }}</h2>
                <ul class="mt-3 divide-y divide-navy-100 text-sm">
                    @foreach ($student->guardians as $guardian)
                        <li class="flex items-center justify-between py-2">
                            <div>
                                <a href="{{ route('admin.guardians.show', $guardian) }}" class="font-medium text-navy hover:underline">{{ $guardian->user->name }}</a>
                                <span class="text-navy-400">— {{ $guardian->pivot->relationship }}{{ $guardian->pivot->is_primary ? ' · '.__('guardian.fields.is_primary') : '' }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.students.guardians.destroy', [$student, $guardian]) }}">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-600 underline">{{ __('common.actions.remove') }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('admin.students.guardians.store', $student) }}" class="mt-4 flex flex-wrap items-end gap-2 border-t border-navy-100 pt-4">
                    @csrf
                    <input type="hidden" name="mode" value="existing">
                    <select name="guardian_id" class="rounded-md border-navy-200 text-sm" required>
                        <option value="">{{ __('admin.student.guardians.add_heading') }}</option>
                    </select>
                    <input name="relationship" placeholder="{{ __('guardian.fields.relationship') }}" class="rounded-md border-navy-200 text-sm">
                    <button class="rounded-md bg-navy px-3 py-1.5 text-sm text-white">{{ __('common.actions.add') }}</button>
                </form>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.student.show.enrolment_heading') }}</h2>
                <p class="mt-3 text-sm">
                    {{ $student->activeEnrolment?->squad?->translated('name') ?? __('common.none') }}
                </p>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.student.show.agreement_heading') }}</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($student->agreementSignatures as $signature)
                        <li class="flex items-center justify-between">
                            <span>v{{ $signature->agreementTemplate->version }} — {{ \App\Support\FormatsDates::date($signature->signed_at) }}</span>
                            <x-status-badge :status="$signature->status->value" :label="$signature->status->label()" />
                        </li>
                    @empty
                        <li class="text-navy-400">{{ __('common.none') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.student.show.credentials_heading') }}</h2>
                <p class="mt-2 text-sm text-navy-500">
                    {{ $student->user?->username ?? __('admin.student.index.login_not_issued') }}
                </p>
                <div class="mt-3">
                    <x-credential-issuer
                        :action="route('admin.students.credentials.issue', $student)"
                        :button-label="__($student->user ? 'admin.credential.reset_button' : 'admin.credential.issue_button')"
                    />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

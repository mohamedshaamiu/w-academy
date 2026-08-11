<x-app-layout portal="admin" :title="$guardian->user->name" :header="$guardian->user->name">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.guardians.edit', $guardian) }}" class="ms-auto rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
            {{ __('common.actions.edit') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.guardian.show.title') }}</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-navy-400">{{ __('common.labels.phone') }}</dt><dd>{{ $guardian->user->phone }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('common.labels.email') }}</dt><dd>{{ $guardian->user->email ?? __('common.na') }}</dd></div>
                    <div class="col-span-2"><dt class="text-navy-400">{{ __('common.labels.address') }}</dt><dd>{{ $guardian->address }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('guardian.fields.national_id') }}</dt><dd>{{ $guardian->national_id ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('guardian.fields.occupation') }}</dt><dd>{{ $guardian->occupation ?? __('common.na') }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.guardian.show.children_heading') }}</h2>
                <ul class="mt-3 divide-y divide-navy-100 text-sm">
                    @forelse ($guardian->students as $student)
                        <li class="py-2">
                            <a href="{{ route('admin.students.show', $student) }}" class="font-medium text-navy hover:underline">{{ $student->full_name }}</a>
                            <span class="text-navy-400">
                                — {{ $student->index_number }} · {{ $student->pivot->relationship }}{{ $student->pivot->is_primary ? ' · '.__('guardian.fields.is_primary') : '' }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-navy-400">{{ __('common.none') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.guardian.show.credentials_heading') }}</h2>
                <p class="mt-2 text-sm text-navy-500">
                    {{ $guardian->user->username }}
                </p>
                <div class="mt-3">
                    <x-credential-issuer
                        :action="route('admin.guardians.credentials.reset', $guardian)"
                        :button-label="__('admin.credential.reset_button')"
                    />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

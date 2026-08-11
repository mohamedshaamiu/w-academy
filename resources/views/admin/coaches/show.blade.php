<x-app-layout portal="admin" :title="$coach->user->name" :header="$coach->user->name">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.coaches.edit', $coach) }}" class="ms-auto rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
            {{ __('common.actions.edit') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.coach.show.title') }}</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-navy-400">{{ __('coach.fields.coach_no') }}</dt><dd class="font-mono">{{ $coach->coach_no }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('common.labels.phone') }}</dt><dd>{{ $coach->user->phone }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('common.labels.email') }}</dt><dd>{{ $coach->user->email ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('coach.fields.specialisation') }}</dt><dd>{{ $coach->specialisation ?? __('common.na') }}</dd></div>
                    <div><dt class="text-navy-400">{{ __('coach.fields.joined_on') }}</dt><dd>{{ \App\Support\FormatsDates::date($coach->joined_on) }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.coach.show.squads_heading') }}</h2>
                <ul class="mt-3 divide-y divide-navy-100 text-sm">
                    @forelse ($coach->squads as $squad)
                        <li class="py-2">
                            <a href="{{ route('admin.squads.show', $squad) }}" class="font-medium text-navy hover:underline">{{ $squad->translated('name') }}</a>
                            <span class="text-navy-400">— {{ $squad->age_group }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-navy-400">{{ __('common.none') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-navy-100 bg-white p-6">
                <h2 class="font-semibold text-navy">{{ __('admin.coach.show.credentials_heading') }}</h2>
                <p class="mt-2 text-sm text-navy-500">
                    {{ $coach->user->username }}
                </p>
                <div class="mt-3">
                    <x-credential-issuer
                        :action="route('admin.coaches.credentials.reset', $coach)"
                        :button-label="__('admin.credential.reset_button')"
                    />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

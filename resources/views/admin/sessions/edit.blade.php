<x-app-layout portal="admin" :title="__('admin.session.edit.title')" :header="__('admin.session.edit.title')">
    <form method="POST" action="{{ route('admin.sessions.update', $session) }}" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="squad_id" :value="__('session.fields.squad')" />
                    <select id="squad_id" name="squad_id" class="mt-1 block w-full rounded-md border-navy-200" required>
                        <option value="">—</option>
                        @foreach ($squads as $squad)
                            <option value="{{ $squad->id }}" @selected((string) old('squad_id', $session->squad_id) === (string) $squad->id)>{{ $squad->translated('name') }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('squad_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="coach_id" :value="__('session.fields.coach')" />
                    <x-text-input id="coach_id" type="number" min="1" name="coach_id" class="mt-1 block w-full" :value="old('coach_id', $session->coach_id)" />
                    <p class="mt-1 text-xs text-navy-400">{{ __('admin.session.coach_id_hint') }}</p>
                    <x-input-error :messages="$errors->get('coach_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="scheduled_start" :value="__('session.fields.scheduled_start')" />
                    <x-text-input id="scheduled_start" type="datetime-local" name="scheduled_start" class="mt-1 block w-full" required :value="old('scheduled_start', $session->scheduled_start?->format('Y-m-d\TH:i'))" />
                    <x-input-error :messages="$errors->get('scheduled_start')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="scheduled_end" :value="__('session.fields.scheduled_end')" />
                    <x-text-input id="scheduled_end" type="datetime-local" name="scheduled_end" class="mt-1 block w-full" required :value="old('scheduled_end', $session->scheduled_end?->format('Y-m-d\TH:i'))" />
                    <x-input-error :messages="$errors->get('scheduled_end')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="venue_dv" :value="__('session.fields.venue').' ('.__('common.locale.dv').')'" />
                    <x-text-input id="venue_dv" name="venue_dv" class="mt-1 block w-full" :value="old('venue_dv', $session->venue_dv)" />
                    <x-input-error :messages="$errors->get('venue_dv')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="venue_en" :value="__('session.fields.venue').' ('.__('common.locale.en').')'" />
                    <x-text-input id="venue_en" name="venue_en" class="mt-1 block w-full" :value="old('venue_en', $session->venue_en)" />
                    <x-input-error :messages="$errors->get('venue_en')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="notes" :value="__('session.fields.notes')" />
                    <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-navy-200">{{ old('notes', $session->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

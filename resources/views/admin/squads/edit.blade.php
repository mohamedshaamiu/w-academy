<x-app-layout portal="admin" :title="__('admin.squad.edit.title')" :header="__('admin.squad.edit.title')">
    @php
        $weekdayLabel = fn (int $isoDay) => __('common.weekday.'.($isoDay === 7 ? 0 : $isoDay));
        $selectedDays = old('training_days', $squad->training_days ?? []);
    @endphp

    <form method="POST" action="{{ route('admin.squads.update', $squad) }}" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="name_dv" :value="__('squad.fields.name').' ('.__('common.locale.dv').')'" />
                    <x-text-input id="name_dv" name="name_dv" class="mt-1 block w-full" required :value="old('name_dv', $squad->name_dv)" />
                    <x-input-error :messages="$errors->get('name_dv')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="name_en" :value="__('squad.fields.name').' ('.__('common.locale.en').')'" />
                    <x-text-input id="name_en" name="name_en" class="mt-1 block w-full" required :value="old('name_en', $squad->name_en)" />
                    <x-input-error :messages="$errors->get('name_en')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="age_group" :value="__('squad.fields.age_group')" />
                    <x-text-input id="age_group" name="age_group" class="mt-1 block w-full" required :value="old('age_group', $squad->age_group)" />
                    <x-input-error :messages="$errors->get('age_group')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="head_coach_id" :value="__('squad.fields.head_coach')" />
                    <select id="head_coach_id" name="head_coach_id" class="mt-1 block w-full rounded-md border-navy-200">
                        <option value="">—</option>
                        @foreach ($coaches as $coach)
                            <option value="{{ $coach->id }}" @selected((string) old('head_coach_id', $squad->head_coach_id) === (string) $coach->id)>{{ $coach->user->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('head_coach_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="venue_dv" :value="__('squad.fields.venue').' ('.__('common.locale.dv').')'" />
                    <x-text-input id="venue_dv" name="venue_dv" class="mt-1 block w-full" :value="old('venue_dv', $squad->venue_dv)" />
                    <x-input-error :messages="$errors->get('venue_dv')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="venue_en" :value="__('squad.fields.venue').' ('.__('common.locale.en').')'" />
                    <x-text-input id="venue_en" name="venue_en" class="mt-1 block w-full" :value="old('venue_en', $squad->venue_en)" />
                    <x-input-error :messages="$errors->get('venue_en')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="default_start_time" :value="__('squad.fields.default_start_time')" />
                    <x-text-input id="default_start_time" type="time" name="default_start_time" class="mt-1 block w-full" :value="old('default_start_time', $squad->default_start_time)" />
                    <x-input-error :messages="$errors->get('default_start_time')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="default_end_time" :value="__('squad.fields.default_end_time')" />
                    <x-text-input id="default_end_time" type="time" name="default_end_time" class="mt-1 block w-full" :value="old('default_end_time', $squad->default_end_time)" />
                    <x-input-error :messages="$errors->get('default_end_time')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="capacity" :value="__('squad.fields.capacity')" />
                    <x-text-input id="capacity" type="number" min="1" name="capacity" class="mt-1 block w-full" :value="old('capacity', $squad->capacity)" />
                    <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $squad->is_active))>
                        {{ __('squad.fields.is_active') }}
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <x-input-label :value="__('squad.fields.training_days')" />
                    <div class="mt-2 flex flex-wrap gap-3">
                        @for ($isoDay = 1; $isoDay <= 7; $isoDay++)
                            <label class="flex items-center gap-2 rounded-md border border-navy-200 px-3 py-1.5 text-sm">
                                <input type="checkbox" name="training_days[]" value="{{ $isoDay }}" @checked(in_array($isoDay, $selectedDays))>
                                {{ $weekdayLabel($isoDay) }}
                            </label>
                        @endfor
                    </div>
                    <x-input-error :messages="$errors->get('training_days')" class="mt-2" />
                </div>
            </div>
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

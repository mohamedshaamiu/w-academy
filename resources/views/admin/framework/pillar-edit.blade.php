<x-app-layout portal="admin" :title="$pillar->translated('name')" :header="$pillar->translated('name')">
    <form method="POST" action="{{ route('admin.framework.pillars.update', $pillar) }}" class="max-w-3xl space-y-4 rounded-xl border border-navy-100 bg-white p-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="name_dv" :value="__('admin.framework.pillars.fields.name').' ('.__('common.locale.dv').')'" />
                <x-text-input id="name_dv" name="name_dv" class="mt-1 block w-full" required :value="old('name_dv', $pillar->name_dv)" />
                <x-input-error :messages="$errors->get('name_dv')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="name_en" :value="__('admin.framework.pillars.fields.name').' ('.__('common.locale.en').')'" />
                <x-text-input id="name_en" name="name_en" class="mt-1 block w-full" required :value="old('name_en', $pillar->name_en)" />
                <x-input-error :messages="$errors->get('name_en')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description_dv" :value="__('admin.framework.pillars.fields.description').' ('.__('common.locale.dv').')'" />
                <textarea id="description_dv" name="description_dv" rows="5" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('description_dv', $pillar->description_dv) }}</textarea>
                <x-input-error :messages="$errors->get('description_dv')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description_en" :value="__('admin.framework.pillars.fields.description').' ('.__('common.locale.en').')'" />
                <textarea id="description_en" name="description_en" rows="5" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('description_en', $pillar->description_en) }}</textarea>
                <x-input-error :messages="$errors->get('description_en')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="icon" :value="__('admin.framework.pillars.fields.icon')" />
                <x-text-input id="icon" name="icon" class="mt-1 block w-full" required :value="old('icon', $pillar->icon)" />
                <x-input-error :messages="$errors->get('icon')" class="mt-2" />
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $pillar->is_active))>
                    {{ __('common.labels.active') }}
                </label>
            </div>
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

<x-app-layout portal="admin" :title="__('admin.agreement.create.title')" :header="__('admin.agreement.create.title')">
    <form
        method="POST"
        action="{{ route('admin.agreement-templates.store') }}"
        class="max-w-4xl space-y-6"
        x-data="{
            clauses: [{ key: '', label_dv: '', label_en: '', required: true }],
            add() { this.clauses.push({ key: '', label_dv: '', label_en: '', required: true }) },
            remove(i) { this.clauses.splice(i, 1) },
        }"
    >
        @csrf

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="version" :value="__('admin.agreement.fields.version')" />
                    <x-text-input id="version" type="number" min="1" name="version" class="mt-1 block w-full" required :value="old('version')" />
                    <x-input-error :messages="$errors->get('version')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="effective_from" :value="__('admin.agreement.fields.effective_from')" />
                    <x-text-input id="effective_from" type="date" name="effective_from" class="mt-1 block w-full" required :value="old('effective_from')" />
                    <x-input-error :messages="$errors->get('effective_from')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="title_dv" :value="__('admin.agreement.fields.title').' ('.__('common.locale.dv').')'" />
                    <x-text-input id="title_dv" name="title_dv" class="mt-1 block w-full" required :value="old('title_dv')" />
                    <x-input-error :messages="$errors->get('title_dv')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="title_en" :value="__('admin.agreement.fields.title').' ('.__('common.locale.en').')'" />
                    <x-text-input id="title_en" name="title_en" class="mt-1 block w-full" required :value="old('title_en')" />
                    <x-input-error :messages="$errors->get('title_en')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="body_dv" :value="__('admin.agreement.fields.body').' ('.__('common.locale.dv').')'" />
                    <textarea id="body_dv" name="body_dv" rows="8" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('body_dv') }}</textarea>
                    <x-input-error :messages="$errors->get('body_dv')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="body_en" :value="__('admin.agreement.fields.body').' ('.__('common.locale.en').')'" />
                    <textarea id="body_en" name="body_en" rows="8" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('body_en') }}</textarea>
                    <x-input-error :messages="$errors->get('body_en')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('admin.agreement.clauses_heading') }}</h2>
            <x-input-error :messages="$errors->get('consent_clauses')" class="mt-2" />

            <template x-for="(c, i) in clauses" :key="i">
                <div class="mt-4 rounded-lg border border-navy-100 p-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input :name="`consent_clauses[${i}][key]`" x-model="c.key" placeholder="{{ __('admin.agreement.fields.clause_key') }}" class="rounded-md border-navy-200 text-sm sm:col-span-2">
                        <input :name="`consent_clauses[${i}][label_dv]`" x-model="c.label_dv" placeholder="{{ __('admin.agreement.fields.clause_label') }} ({{ __('common.locale.dv') }})" class="rounded-md border-navy-200 text-sm">
                        <input :name="`consent_clauses[${i}][label_en]`" x-model="c.label_en" placeholder="{{ __('admin.agreement.fields.clause_label') }} ({{ __('common.locale.en') }})" class="rounded-md border-navy-200 text-sm">

                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" :name="`consent_clauses[${i}][required]`" :value="c.required ? 1 : 0">
                            <input type="checkbox" x-model="c.required">
                            {{ __('common.required') }}
                        </label>
                    </div>

                    <button type="button" @click="remove(i)" x-show="clauses.length > 1" class="mt-3 text-sm text-red-600 underline">
                        {{ __('common.actions.remove') }}
                    </button>
                </div>
            </template>

            <button type="button" @click="add()" class="mt-4 text-sm font-medium text-navy underline">
                + {{ __('common.actions.add') }}
            </button>
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

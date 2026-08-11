<x-app-layout portal="admin" :title="$level->translated('label')" :header="$level->translated('label')">
    <form method="POST" action="{{ route('admin.framework.strike-levels.update', $level) }}" class="max-w-3xl space-y-6 rounded-xl border border-navy-100 bg-white p-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="label_dv" :value="__('admin.framework.strike_levels.fields.label').' ('.__('common.locale.dv').')'" />
                <x-text-input id="label_dv" name="label_dv" class="mt-1 block w-full" required :value="old('label_dv', $level->label_dv)" />
                <x-input-error :messages="$errors->get('label_dv')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="label_en" :value="__('admin.framework.strike_levels.fields.label').' ('.__('common.locale.en').')'" />
                <x-text-input id="label_en" name="label_en" class="mt-1 block w-full" required :value="old('label_en', $level->label_en)" />
                <x-input-error :messages="$errors->get('label_en')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="type_dv" :value="__('framework.page.table.type').' ('.__('common.locale.dv').')'" />
                <x-text-input id="type_dv" name="type_dv" class="mt-1 block w-full" required :value="old('type_dv', $level->type_dv)" />
                <x-input-error :messages="$errors->get('type_dv')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="type_en" :value="__('framework.page.table.type').' ('.__('common.locale.en').')'" />
                <x-text-input id="type_en" name="type_en" class="mt-1 block w-full" required :value="old('type_en', $level->type_en)" />
                <x-input-error :messages="$errors->get('type_en')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="action_dv" :value="__('framework.page.table.action').' ('.__('common.locale.dv').')'" />
                <textarea id="action_dv" name="action_dv" rows="4" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('action_dv', $level->action_dv) }}</textarea>
                <x-input-error :messages="$errors->get('action_dv')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="action_en" :value="__('framework.page.table.action').' ('.__('common.locale.en').')'" />
                <textarea id="action_en" name="action_en" rows="4" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('action_en', $level->action_en) }}</textarea>
                <x-input-error :messages="$errors->get('action_en')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="parent_role_dv" :value="__('framework.page.table.parent_role').' ('.__('common.locale.dv').')'" />
                <textarea id="parent_role_dv" name="parent_role_dv" rows="4" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('parent_role_dv', $level->parent_role_dv) }}</textarea>
                <x-input-error :messages="$errors->get('parent_role_dv')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="parent_role_en" :value="__('framework.page.table.parent_role').' ('.__('common.locale.en').')'" />
                <textarea id="parent_role_en" name="parent_role_en" rows="4" class="mt-1 block w-full rounded-md border-navy-200" required>{{ old('parent_role_en', $level->parent_role_en) }}</textarea>
                <x-input-error :messages="$errors->get('parent_role_en')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 border-t border-navy-100 pt-4 sm:grid-cols-2">
            <div>
                <x-input-label for="timeout_minutes_min" :value="__('admin.framework.strike_levels.fields.timeout_minutes_min')" />
                <x-text-input id="timeout_minutes_min" type="number" min="1" name="timeout_minutes_min" class="mt-1 block w-full" :value="old('timeout_minutes_min', $level->timeout_minutes_min)" />
                <x-input-error :messages="$errors->get('timeout_minutes_min')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="timeout_minutes_max" :value="__('admin.framework.strike_levels.fields.timeout_minutes_max')" />
                <x-text-input id="timeout_minutes_max" type="number" min="1" name="timeout_minutes_max" class="mt-1 block w-full" :value="old('timeout_minutes_max', $level->timeout_minutes_max)" />
                <x-input-error :messages="$errors->get('timeout_minutes_max')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-t border-navy-100 pt-4 sm:grid-cols-2">
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="triggers_timeout" value="0">
                <input type="checkbox" name="triggers_timeout" value="1" @checked(old('triggers_timeout', $level->triggers_timeout))>
                {{ __('admin.framework.strike_levels.fields.triggers_timeout') }}
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="triggers_parent_alert" value="0">
                <input type="checkbox" name="triggers_parent_alert" value="1" @checked(old('triggers_parent_alert', $level->triggers_parent_alert))>
                {{ __('admin.framework.strike_levels.fields.triggers_parent_alert') }}
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="triggers_meeting" value="0">
                <input type="checkbox" name="triggers_meeting" value="1" @checked(old('triggers_meeting', $level->triggers_meeting))>
                {{ __('admin.framework.strike_levels.fields.triggers_meeting') }}
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="triggers_suspension" value="0">
                <input type="checkbox" name="triggers_suspension" value="1" @checked(old('triggers_suspension', $level->triggers_suspension))>
                {{ __('admin.framework.strike_levels.fields.triggers_suspension') }}
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $level->is_active))>
                {{ __('common.labels.active') }}
            </label>
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

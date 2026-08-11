<x-app-layout portal="admin" :title="__('admin.student.create.title')" :header="__('admin.student.create.title')">
    <form
        method="POST"
        action="{{ route('admin.students.store') }}"
        enctype="multipart/form-data"
        class="max-w-3xl space-y-6"
        x-data="{
            guardians: [{ mode: 'existing', guardian_id: '', name: '', phone: '', address: '', relationship: '', is_primary: true, receives_alerts: true }],
            add() { this.guardians.push({ mode: 'existing', guardian_id: '', name: '', phone: '', address: '', relationship: '', is_primary: false, receives_alerts: true }) },
            remove(i) { this.guardians.splice(i, 1) },
        }"
    >
        @csrf

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('admin.student.index.title') }}</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="index_number" :value="__('student.fields.index_number')" />
                    <x-text-input id="index_number" name="index_number" class="mt-1 block w-full" required :value="old('index_number')" />
                    <x-input-error :messages="$errors->get('index_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="full_name" :value="__('student.fields.full_name')" />
                    <x-text-input id="full_name" name="full_name" class="mt-1 block w-full" required :value="old('full_name')" />
                    <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="date_of_birth" :value="__('student.fields.date_of_birth')" />
                    <x-text-input id="date_of_birth" type="date" name="date_of_birth" class="mt-1 block w-full" required :value="old('date_of_birth')" />
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="gender" :value="__('student.fields.gender')" />
                    <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-navy-200" required>
                        <option value="male" @selected(old('gender') === 'male')>{{ __('student.fields.gender_male') }}</option>
                        <option value="female" @selected(old('gender') === 'female')>{{ __('student.fields.gender_female') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="school_name" :value="__('student.fields.school_name')" />
                    <x-text-input id="school_name" name="school_name" class="mt-1 block w-full" :value="old('school_name')" />
                </div>

                <div>
                    <x-input-label for="class_level" :value="__('student.fields.class_level')" />
                    <x-text-input id="class_level" name="class_level" class="mt-1 block w-full" :value="old('class_level')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="address" :value="__('student.fields.address')" />
                    <x-text-input id="address" name="address" class="mt-1 block w-full" required :value="old('address')" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="photo" :value="__('student.fields.photo')" />
                    <input id="photo" type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('admin.student.guardians.add_heading') }}</h2>
            <x-input-error :messages="$errors->get('guardians')" class="mt-2" />

            <template x-for="(g, i) in guardians" :key="i">
                <div class="mt-4 rounded-lg border border-navy-100 p-4">
                    <div class="flex gap-4 text-sm">
                        <label class="flex items-center gap-1">
                            <input type="radio" :name="`guardians[${i}][mode]`" value="existing" x-model="g.mode">
                            {{ __('common.actions.link_existing') }}
                        </label>
                        <label class="flex items-center gap-1">
                            <input type="radio" :name="`guardians[${i}][mode]`" value="new" x-model="g.mode">
                            {{ __('common.actions.create_new') }}
                        </label>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <template x-if="g.mode === 'existing'">
                            <select :name="`guardians[${i}][guardian_id]`" x-model="g.guardian_id" class="rounded-md border-navy-200 text-sm sm:col-span-2">
                                <option value="">—</option>
                                @foreach ($guardians as $existing)
                                    <option value="{{ $existing->id }}">{{ $existing->user->name }} ({{ $existing->user->phone }})</option>
                                @endforeach
                            </select>
                        </template>

                        <template x-if="g.mode === 'new'">
                            <input :name="`guardians[${i}][name]`" x-model="g.name" placeholder="{{ __('common.labels.name') }}" class="rounded-md border-navy-200 text-sm">
                        </template>
                        <template x-if="g.mode === 'new'">
                            <input :name="`guardians[${i}][phone]`" x-model="g.phone" placeholder="{{ __('common.labels.phone') }}" class="rounded-md border-navy-200 text-sm">
                        </template>
                        <template x-if="g.mode === 'new'">
                            <input :name="`guardians[${i}][address]`" x-model="g.address" placeholder="{{ __('common.labels.address') }}" class="rounded-md border-navy-200 text-sm sm:col-span-2">
                        </template>

                        <input :name="`guardians[${i}][relationship]`" x-model="g.relationship" placeholder="{{ __('guardian.fields.relationship') }}" class="rounded-md border-navy-200 text-sm">

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" :name="`guardians[${i}][is_primary]`" value="1" x-model="g.is_primary">
                            {{ __('guardian.fields.is_primary') }}
                        </label>
                    </div>

                    <button type="button" @click="remove(i)" x-show="guardians.length > 1" class="mt-3 text-sm text-red-600 underline">
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

<x-app-layout portal="admin" :title="__('admin.student.edit.title')" :header="__('admin.student.edit.title')">
    <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-xl border border-navy-100 bg-white p-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="index_number" :value="__('student.fields.index_number')" />
            <x-text-input id="index_number" name="index_number" class="mt-1 block w-full" required :value="old('index_number', $student->index_number)" />
            <x-input-error :messages="$errors->get('index_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="full_name" :value="__('student.fields.full_name')" />
            <x-text-input id="full_name" name="full_name" class="mt-1 block w-full" required :value="old('full_name', $student->full_name)" />
            <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="date_of_birth" :value="__('student.fields.date_of_birth')" />
            <x-text-input id="date_of_birth" type="date" name="date_of_birth" class="mt-1 block w-full" required :value="old('date_of_birth', $student->date_of_birth->toDateString())" />
            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="gender" :value="__('student.fields.gender')" />
            <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-navy-200" required>
                <option value="male" @selected(old('gender', $student->gender) === 'male')>{{ __('student.fields.gender_male') }}</option>
                <option value="female" @selected(old('gender', $student->gender) === 'female')>{{ __('student.fields.gender_female') }}</option>
            </select>
        </div>

        <div>
            <x-input-label for="school_name" :value="__('student.fields.school_name')" />
            <x-text-input id="school_name" name="school_name" class="mt-1 block w-full" :value="old('school_name', $student->school_name)" />
        </div>

        <div>
            <x-input-label for="class_level" :value="__('student.fields.class_level')" />
            <x-text-input id="class_level" name="class_level" class="mt-1 block w-full" :value="old('class_level', $student->class_level)" />
        </div>

        <div>
            <x-input-label for="address" :value="__('student.fields.address')" />
            <x-text-input id="address" name="address" class="mt-1 block w-full" required :value="old('address', $student->address)" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="photo" :value="__('student.fields.photo')" />
            <input id="photo" type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm">
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

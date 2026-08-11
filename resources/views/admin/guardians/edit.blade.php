<x-app-layout portal="admin" :title="__('admin.guardian.edit.title')" :header="__('admin.guardian.edit.title')">
    <form method="POST" action="{{ route('admin.guardians.update', $guardian) }}" class="max-w-2xl space-y-4 rounded-xl border border-navy-100 bg-white p-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="name" :value="__('common.labels.name')" />
            <x-text-input id="name" name="name" class="mt-1 block w-full" required :value="old('name', $guardian->user->name)" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('common.labels.phone')" />
            <x-text-input id="phone" name="phone" class="mt-1 block w-full" required :value="old('phone', $guardian->user->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('common.labels.email')" />
            <x-text-input id="email" type="email" name="email" class="mt-1 block w-full" :value="old('email', $guardian->user->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="address" :value="__('common.labels.address')" />
            <x-text-input id="address" name="address" class="mt-1 block w-full" required :value="old('address', $guardian->address)" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="national_id" :value="__('guardian.fields.national_id')" />
            <x-text-input id="national_id" name="national_id" class="mt-1 block w-full" :value="old('national_id', $guardian->national_id)" />
            <x-input-error :messages="$errors->get('national_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="occupation" :value="__('guardian.fields.occupation')" />
            <x-text-input id="occupation" name="occupation" class="mt-1 block w-full" :value="old('occupation', $guardian->occupation)" />
            <x-input-error :messages="$errors->get('occupation')" class="mt-2" />
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

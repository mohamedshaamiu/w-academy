<x-app-layout portal="admin" :title="__('admin.coach.edit.title')" :header="__('admin.coach.edit.title')">
    <form method="POST" action="{{ route('admin.coaches.update', $coach) }}" class="max-w-2xl space-y-4 rounded-xl border border-navy-100 bg-white p-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="name" :value="__('common.labels.name')" />
            <x-text-input id="name" name="name" class="mt-1 block w-full" required :value="old('name', $coach->user->name)" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('common.labels.phone')" />
            <x-text-input id="phone" name="phone" class="mt-1 block w-full" required :value="old('phone', $coach->user->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('common.labels.email')" />
            <x-text-input id="email" type="email" name="email" class="mt-1 block w-full" :value="old('email', $coach->user->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="specialisation" :value="__('coach.fields.specialisation')" />
            <x-text-input id="specialisation" name="specialisation" class="mt-1 block w-full" :value="old('specialisation', $coach->specialisation)" />
            <x-input-error :messages="$errors->get('specialisation')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="joined_on" :value="__('coach.fields.joined_on')" />
            <x-text-input id="joined_on" type="date" name="joined_on" class="mt-1 block w-full" required :value="old('joined_on', $coach->joined_on->toDateString())" />
            <x-input-error :messages="$errors->get('joined_on')" class="mt-2" />
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

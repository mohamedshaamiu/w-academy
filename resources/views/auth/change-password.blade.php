<x-guest-layout>
    <h1 class="mb-2 text-center text-lg font-bold text-navy">{{ __('auth.change_password.title') }}</h1>
    <p class="mb-6 text-center text-sm text-navy-400">{{ __('auth.change_password.notice') }}</p>

    <form method="POST" action="{{ route('password.change.update') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="current_password" :value="__('auth.change_password.current_password')" />
            <x-text-input id="current_password" class="mt-1 block w-full" type="password" name="current_password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('auth.change_password.new_password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('auth.change_password.confirm_password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <x-primary-button class="w-full">
            {{ __('auth.change_password.submit') }}
        </x-primary-button>
    </form>
</x-guest-layout>

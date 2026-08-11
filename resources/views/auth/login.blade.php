<x-guest-layout>
    <h1 class="mb-6 text-center text-lg font-bold text-navy">{{ __('common.app_name') }}</h1>

    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="username" :value="__('auth.login.identifier')" />
            <x-text-input id="username" class="mt-1 block w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('common.labels.password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label class="flex items-center gap-2 text-sm text-navy-500">
            <input type="checkbox" name="remember" class="rounded border-navy-200 text-navy focus:ring-gold">
            {{ __('auth.login.remember_me') }}
        </label>

        <x-primary-button class="w-full">
            {{ __('auth.login.submit') }}
        </x-primary-button>
    </form>
</x-guest-layout>

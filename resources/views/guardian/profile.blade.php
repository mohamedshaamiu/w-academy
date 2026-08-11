<x-app-layout portal="guardian" :title="__('guardian.profile.title')" :header="__('guardian.profile.title')">
    <form method="POST" action="{{ route('guardian.profile.update') }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PATCH')

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                    <x-input-label for="locale" :value="__('student.profile.language')" />
                    <select id="locale" name="locale" class="mt-1 block w-full rounded-md border-navy-200" required>
                        @foreach (\App\Enums\Locale::cases() as $locale)
                            <option value="{{ $locale->value }}" @selected(old('locale', $guardian->user->locale) === $locale->value)>
                                {{ $locale->label() }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('locale')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="address" :value="__('common.labels.address')" />
                    <x-text-input id="address" name="address" class="mt-1 block w-full" required :value="old('address', $guardian->address)" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('guardian.profile.password_section') }}</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="current_password" :value="__('auth.change_password.current_password')" />
                    <x-text-input id="current_password" type="password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                </div>

                <div class="hidden sm:block"></div>

                <div>
                    <x-input-label for="password" :value="__('auth.change_password.new_password')" />
                    <x-text-input id="password" type="password" name="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('auth.change_password.confirm_password')" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" class="mt-1 block w-full" autocomplete="new-password" />
                </div>
            </div>
        </div>

        <x-primary-button>{{ __('common.actions.save') }}</x-primary-button>
    </form>
</x-app-layout>

<x-app-layout portal="student" :title="__('student.profile.title')" :header="__('student.profile.title')">
    <div class="max-w-xl space-y-6">
        <div class="rounded-xl border border-navy-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <img src="{{ route('students.photo', $student) }}" alt="" class="h-16 w-16 shrink-0 rounded-full bg-navy-50 object-cover" onerror="this.style.display='none'">
                <div>
                    <p class="text-lg font-semibold text-navy">{{ $student->full_name }}</p>
                    <p class="text-sm text-navy-400">{{ $student->index_number }}</p>
                </div>
            </div>

            <dl class="mt-4 space-y-2 border-t border-navy-100 pt-4 text-sm text-navy-500">
                <div class="flex justify-between gap-4">
                    <dt>{{ __('student.fields.date_of_birth') }}</dt>
                    <dd class="font-medium text-navy">{{ \App\Support\FormatsDates::date($student->date_of_birth) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ __('student.fields.age') }}</dt>
                    <dd class="font-medium text-navy">{{ $student->age }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ __('student.fields.school_name') }}</dt>
                    <dd class="font-medium text-navy">{{ $student->school_name ?? __('common.na') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ __('student.fields.class_level') }}</dt>
                    <dd class="font-medium text-navy">{{ $student->class_level ?? __('common.na') }}</dd>
                </div>
            </dl>

            <p class="mt-4 text-xs text-navy-400">{{ __('student.profile.readonly_notice') }}</p>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="locale" :value="__('student.profile.language')" />
                    <select id="locale" name="locale" class="mt-1 block w-full rounded-md border-navy-200 text-sm">
                        @foreach (\App\Enums\Locale::cases() as $locale)
                            <option value="{{ $locale->value }}" @selected(old('locale', auth()->user()->locale?->value) === $locale->value)>{{ $locale->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('locale')" class="mt-2" />
                </div>

                <div class="border-t border-navy-100 pt-4">
                    <p class="mb-3 text-sm font-semibold text-navy">{{ __('student.profile.password_section') }}</p>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="current_password" :value="__('auth.change_password.current_password')" />
                            <x-text-input id="current_password" class="mt-1 block w-full" type="password" name="current_password" autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('auth.change_password.new_password')" />
                            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('auth.change_password.confirm_password')" />
                            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                        </div>
                    </div>
                </div>

                <x-primary-button>
                    {{ __('common.actions.save') }}
                </x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>

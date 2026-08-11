<x-guest-layout :title="__('agreement.page.title')">
    <div class="w-full max-w-2xl">
        <h1 class="text-lg font-bold text-navy">{{ __('agreement.page.title') }}</h1>
        <p class="mt-1 text-sm text-navy-400">
            {{ __('agreement.page.signing_for') }}: <span class="font-semibold text-navy">{{ $student->full_name }}</span>
            ({{ $student->index_number }})
        </p>

        <div class="mt-4 flex items-center justify-between rounded-md bg-navy-50 px-4 py-3">
            <p class="text-sm text-navy-500">{{ __('agreement.page.read_notice') }}</p>
            <x-lang-switcher />
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-md bg-red-50 px-4 py-3">
                <x-input-error :messages="$errors->all()" />
            </div>
        @endif

        <div class="mt-4 max-h-96 overflow-y-auto rounded-md border border-navy-100 p-4 text-sm leading-relaxed text-navy-700">
            <h2 class="mb-2 font-bold text-navy">{{ $template->translated('title') }}</h2>
            {!! nl2br(e($template->translated('body'))) !!}
        </div>

        <form method="POST" action="{{ route('agreement.sign', $student) }}" class="mt-6 space-y-6">
            @csrf

            <div>
                <h3 class="font-semibold text-navy">{{ __('agreement.page.clauses_heading') }}</h3>
                @php($clauseLocale = app()->getLocale())
                <div class="mt-2 space-y-3">
                    @foreach ($template->consent_clauses as $clause)
                        <label class="flex items-start gap-3 text-sm">
                            <input
                                type="checkbox"
                                name="consents[{{ $clause['key'] }}]"
                                value="1"
                                {{ $clause['required'] ? 'required' : '' }}
                                class="mt-1 rounded border-navy-200 text-navy focus:ring-gold"
                            >
                            <span>
                                {{ $clause["label_{$clauseLocale}"] }}{{ $clause['required'] ? ' *' : '' }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <x-input-label for="signatory_name" :value="__('agreement.page.signatory_name_label')" />
                <x-text-input id="signatory_name" name="signatory_name" class="mt-1 block w-full" required minlength="3" :value="old('signatory_name')" />
            </div>

            <div>
                <x-input-label :value="__('agreement.page.signature_pad_label')" />
                <x-signature-pad />
            </div>

            <x-primary-button class="w-full">
                {{ __('agreement.page.submit') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>

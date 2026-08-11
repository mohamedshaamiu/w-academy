@props(['action', 'buttonLabel'])

<div
    x-data="{
        open: false,
        loading: false,
        html: '',
        async issue() {
            this.loading = true;
            const response = await fetch('{{ $action }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'text/html',
                },
            });
            this.html = await response.text();
            this.loading = false;
            this.open = true;
        },
        copyPassword() {
            const el = this.$refs.passwordText;
            navigator.clipboard.writeText(el.textContent.trim());
        },
    }"
>
    <button type="button" @click="issue()" :disabled="loading" class="inline-flex items-center rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-600 disabled:opacity-50">
        {{ $buttonLabel }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-navy-900/50 px-4" @click.self="open = false">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="font-bold text-navy">{{ __('admin.credential.modal_title') }}</h3>
            <div class="mt-4 space-y-3 text-sm" x-html="html"></div>
            <p class="mt-4 text-xs text-red-600">{{ __('admin.credential.copy_warning') }}</p>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="copyPassword()" class="rounded-md border border-navy-200 px-3 py-1.5 text-sm font-medium text-navy hover:bg-navy-50">
                    {{ __('common.actions.copy') }}
                </button>
                <button type="button" @click="open = false" class="rounded-md bg-navy px-3 py-1.5 text-sm font-medium text-white hover:bg-navy-600">
                    {{ __('common.actions.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

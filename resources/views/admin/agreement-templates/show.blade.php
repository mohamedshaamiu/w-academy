<x-app-layout portal="admin" :title="__('admin.agreement.show.title').' v'.$template->version" :header="__('admin.agreement.show.title').' v'.$template->version">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        @if ($template->is_current)
            <span class="inline-flex items-center rounded-full bg-gold-100 px-2.5 py-0.5 text-xs font-semibold text-navy-900">
                {{ __('admin.agreement.current_badge') }}
            </span>
        @endif

        <div class="ms-auto flex flex-wrap gap-2">
            <a href="{{ route('admin.agreement-templates.edit', $template) }}" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
                {{ __('common.actions.edit') }}
            </a>

            @unless ($template->is_current)
                <form method="POST" action="{{ route('admin.agreement-templates.publish', $template) }}" onsubmit="return confirm('{{ __('admin.agreement.publish_confirm') }}')">
                    @csrf
                    <button type="submit" class="rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
                        {{ __('admin.agreement.publish_button') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.agreement-templates.destroy', $template) }}" onsubmit="return confirm('{{ __('common.confirm_prompt') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                        {{ __('common.actions.delete') }}
                    </button>
                </form>
            @endunless
        </div>
    </div>

    <div class="rounded-xl border border-navy-100 bg-white p-6">
        <dl class="grid grid-cols-2 gap-3 text-sm sm:max-w-md">
            <div><dt class="text-navy-400">{{ __('admin.agreement.fields.version') }}</dt><dd class="font-mono">{{ $template->version }}</dd></div>
            <div><dt class="text-navy-400">{{ __('admin.agreement.fields.effective_from') }}</dt><dd>{{ \App\Support\FormatsDates::date($template->effective_from) }}</dd></div>
        </dl>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('common.locale.dv') }}</h2>

            <p class="mt-3 font-semibold text-navy">{{ __('admin.agreement.fields.title') }}</p>
            <p class="mt-1 text-sm">{{ $template->title_dv }}</p>

            <p class="mt-4 font-semibold text-navy">{{ __('admin.agreement.fields.body') }}</p>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $template->body_dv }}</p>

            <p class="mt-4 font-semibold text-navy">{{ __('admin.agreement.clauses_heading') }}</p>
            <ul class="mt-2 space-y-2 text-sm">
                @forelse ($template->consent_clauses ?? [] as $clause)
                    <li class="flex items-start justify-between gap-2">
                        <span>{{ $clause['label_dv'] ?? '' }}</span>
                        @if ($clause['required'] ?? false)
                            <span class="shrink-0 text-xs text-navy-400">{{ __('common.required') }}</span>
                        @endif
                    </li>
                @empty
                    <li class="text-navy-400">{{ __('common.none') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl border border-navy-100 bg-white p-6">
            <h2 class="font-semibold text-navy">{{ __('common.locale.en') }}</h2>

            <p class="mt-3 font-semibold text-navy">{{ __('admin.agreement.fields.title') }}</p>
            <p class="mt-1 text-sm">{{ $template->title_en }}</p>

            <p class="mt-4 font-semibold text-navy">{{ __('admin.agreement.fields.body') }}</p>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $template->body_en }}</p>

            <p class="mt-4 font-semibold text-navy">{{ __('admin.agreement.clauses_heading') }}</p>
            <ul class="mt-2 space-y-2 text-sm">
                @forelse ($template->consent_clauses ?? [] as $clause)
                    <li class="flex items-start justify-between gap-2">
                        <span>{{ $clause['label_en'] ?? '' }}</span>
                        @if ($clause['required'] ?? false)
                            <span class="shrink-0 text-xs text-navy-400">{{ __('common.required') }}</span>
                        @endif
                    </li>
                @empty
                    <li class="text-navy-400">{{ __('common.none') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>

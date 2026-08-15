{{--
    The four life pillars, read from framework_pillars (SPEC.md §8.8 — the
    reference content is table-driven, so nothing here assumes how many there
    are or what they are called).

    Expects: $pillars
--}}
<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="max-w-2xl">
        <p class="text-sm font-semibold uppercase tracking-widest text-gold-700">{{ __('framework.page.pillars_heading') }}</p>
        <h2 class="mt-2 text-2xl font-bold text-navy sm:text-3xl">{{ __('public.pillars.heading') }}</h2>
        <p class="mt-3 leading-relaxed text-navy-500">{{ __('public.pillars.body') }}</p>
    </div>

    <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($pillars as $pillar)
            <article class="group relative overflow-hidden rounded-2xl border border-navy-100 bg-white p-6 transition hover:-translate-y-1 hover:border-gold-200 hover:shadow-lg motion-reduce:hover:translate-y-0">
                <span class="absolute inset-x-0 top-0 h-1 bg-gold opacity-0 transition group-hover:opacity-100" aria-hidden="true"></span>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-navy text-gold">
                    <x-pillar-icon :icon="$pillar->icon" class="h-6 w-6" />
                </div>

                <h3 class="mt-5 font-semibold text-navy">{{ $pillar->translated('name') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-navy-500">{{ $pillar->translated('description') }}</p>

                @if ($pillar->isTranslationFallback('description'))
                    <p class="mt-2 text-xs text-navy-300">{{ __('common.not_available_in_language') }}</p>
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ route('framework') }}" class="inline-flex items-center gap-2 font-semibold text-navy underline decoration-gold decoration-2 underline-offset-4 transition hover:text-gold-700">
            {{ __('public.pillars.cta') }}
            <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>
</section>

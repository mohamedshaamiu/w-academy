{{--
    Closing band. White copy over a photograph, so the navy scrim carries the
    contrast — keep it if the imagery changes.
--}}
<section class="relative isolate overflow-hidden py-16 text-white">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <x-site-photo
            name="beach-line"
            sizes="100vw"
            class="block h-full w-full"
            img-class="h-full w-full object-cover object-center"
        />
        <div class="absolute inset-0 bg-gradient-to-b from-navy-900/85 to-navy-900/90"></div>
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
    </div>

    <div class="mx-auto flex max-w-6xl flex-col items-start gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="max-w-xl">
            <h2 class="text-2xl font-bold">{{ __('public.cta.heading') }}</h2>
            <p class="mt-3 leading-relaxed text-navy-50">{{ __('public.cta.body') }}</p>
        </div>

        <a href="{{ route('contact') }}" class="inline-flex shrink-0 items-center gap-2 rounded-md bg-gold px-6 py-3 text-sm font-semibold text-navy-900 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-200">
            {{ __('public.cta.action') }}
            <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>
</section>

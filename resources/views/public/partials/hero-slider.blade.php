@php
    // The slide set is defined entirely in lang/{dv,en}/public.php, so adding
    // or removing a slide is a translation change, not a template change.
    $slideKeys = array_keys(__('public.hero.slides'));
    $slides = array_values(__('public.hero.slides'));

    // Photography per slide, keyed by the slide's translation key. A slide
    // added in lang/ that is not named here falls back to the rotation, so a
    // translation change still cannot break this template.
    $rotation = ['training-stretch', 'training-lineup', 'beach-squad'];
    $slidePhoto = [
        'pillars' => 'training-stretch',
        'framework' => 'training-lineup',
        'families' => 'beach-squad',
    ];
@endphp

{{--
    Photographic hero, built from the academy's own photos (CLAUDE.md
    § Photography). Each slide carries its own image; image and copy cross-fade
    on the same signal, so the pairing never breaks mid-transition. Everything
    stays in one grid cell, so the section is as tall as its tallest slide and
    nothing jumps.

    White copy sits over a photograph, so the navy scrim below is load-bearing
    for contrast rather than decoration — keep it if you change the imagery.

    Without JavaScript the first slide renders and the rest sit at opacity 0 —
    the page is still readable. Alpine takes over the opacity once it boots.
--}}
<section
    class="relative isolate overflow-hidden bg-navy text-white"
    x-data="{
        active: 0,
        count: {{ count($slides) }},
        timer: null,
        reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        start() {
            if (this.reduced || this.count < 2) return;
            this.stop();
            this.timer = setInterval(() => this.next(), 7000);
        },
        stop() {
            clearInterval(this.timer);
            this.timer = null;
        },
        next() { this.active = (this.active + 1) % this.count; },
        prev() { this.active = (this.active + this.count - 1) % this.count; },
        go(index) { this.active = index; this.start(); },
    }"
    x-init="start()"
    x-on:mouseenter="stop()"
    x-on:mouseleave="start()"
    x-on:focusin="stop()"
    x-on:focusout="start()"
    aria-roledescription="carousel"
    aria-label="{{ __('public.hero.label') }}"
>
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute inset-0 bg-navy-700"></div>

        {{-- One photo per slide, fading on the same signal as the copy. --}}
        @foreach ($slides as $index => $slide)
            <div
                class="absolute inset-0 transition-opacity duration-700 motion-reduce:transition-none"
                style="opacity: {{ $index === 0 ? '1' : '0' }};"
                :style="{ opacity: active === {{ $index }} ? 1 : 0 }"
            >
                <x-site-photo
                    :name="$slidePhoto[$slideKeys[$index]] ?? $rotation[$index % count($rotation)]"
                    :priority="$index === 0"
                    sizes="100vw"
                    class="block h-full w-full"
                    img-class="h-full w-full object-cover object-center"
                />
            </div>
        @endforeach

        {{-- The scrim. Vertical on purpose: a vertical gradient needs no
             mirroring, so it behaves identically under rtl and ltr. --}}
        <div class="absolute inset-0 bg-gradient-to-b from-navy-900/85 via-navy-900/75 to-navy-900/90"></div>

        {{-- Pitch-line motif, kept from the pre-photography hero but dropped to
             a whisper so it reads as a watermark over the image. Logical inset,
             so it mirrors with the document. --}}
        <svg class="absolute -top-24 -end-32 h-[36rem] w-[36rem] text-gold opacity-[0.07]" viewBox="0 0 400 400" fill="none" stroke="currentColor">
            <circle cx="200" cy="200" r="120" stroke-width="1.5" />
            <circle cx="200" cy="200" r="180" stroke-width="1" />
            <circle cx="200" cy="200" r="6" fill="currentColor" stroke="none" />
            <path d="M20 200H380" stroke-width="1" />
        </svg>

        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
    </div>

    <div class="mx-auto grid max-w-6xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
        @foreach ($slides as $index => $slide)
            <div
                class="col-start-1 row-start-1 transition-opacity duration-700 motion-reduce:transition-none"
                style="opacity: {{ $index === 0 ? '1' : '0' }};{{ $index === 0 ? '' : ' pointer-events: none;' }}"
                :style="{ opacity: active === {{ $index }} ? 1 : 0, pointerEvents: active === {{ $index }} ? 'auto' : 'none' }"
                :aria-hidden="active === {{ $index }} ? 'false' : 'true'"
                role="group"
                aria-roledescription="slide"
            >
                <p class="text-sm font-semibold uppercase tracking-widest text-gold-300">{{ $slide['eyebrow'] }}</p>
                <h1 class="mt-4 max-w-2xl text-3xl font-bold leading-tight sm:text-5xl">{{ $slide['title'] }}</h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-navy-50 sm:text-lg">{{ $slide['body'] }}</p>
            </div>
        @endforeach

        {{-- Shared across slides, so keyboard focus never lands inside a
             faded-out slide. --}}
        <div class="col-start-1 row-start-2 mt-10 flex flex-wrap items-center gap-3">
            <a href="{{ route('framework') }}" class="inline-flex items-center gap-2 rounded-md bg-gold px-5 py-3 text-sm font-semibold text-navy-900 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-200">
                {{ __('public.hero.primary_cta') }}
                <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center rounded-md border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:border-gold hover:text-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-200">
                {{ __('public.hero.secondary_cta') }}
            </a>
        </div>

        @if (count($slides) > 1)
            <div class="col-start-1 row-start-3 mt-10 flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        x-on:click="prev(); start()"
                        class="rounded-full border border-white/25 p-2 transition hover:border-gold hover:text-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-200"
                        aria-label="{{ __('public.hero.previous') }}"
                    >
                        <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        x-on:click="next(); start()"
                        class="rounded-full border border-white/25 p-2 transition hover:border-gold hover:text-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-200"
                        aria-label="{{ __('public.hero.next') }}"
                    >
                        <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    @foreach ($slides as $index => $slide)
                        <button
                            type="button"
                            x-on:click="go({{ $index }})"
                            class="hero-dot {{ $index === 0 ? 'hero-dot--active' : '' }}"
                            :class="active === {{ $index }} ? 'hero-dot--active' : ''"
                            aria-label="{{ __('public.hero.go_to') }} {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

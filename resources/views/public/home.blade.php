<x-public-layout>
    @include('public.partials.hero-slider')

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-12">
            {{-- The photo leads on the start edge and mirrors with the
                 document, so the copy always follows the image in reading
                 order under both directions. --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    <x-site-photo
                        name="squad-night"
                        sizes="(min-width: 1024px) 40vw, 100vw"
                        class="block overflow-hidden rounded-2xl shadow-lg ring-1 ring-navy-100"
                        img-class="aspect-[4/3] w-full object-cover"
                    />
                    <span class="absolute -bottom-3 -start-3 -z-10 h-24 w-24 rounded-2xl bg-gold/25" aria-hidden="true"></span>
                </div>
            </div>

            <div class="lg:col-span-7">
                <h2 class="text-2xl font-bold leading-snug text-navy sm:text-3xl">{{ __('public.intro.heading') }}</h2>
                <div class="mt-4 h-1 w-16 rounded-full bg-gold" aria-hidden="true"></div>

                <p class="mt-6 text-lg leading-relaxed text-navy-500">{{ __('public.intro.body') }}</p>

                <a href="{{ route('about') }}" class="mt-6 inline-flex items-center gap-2 font-semibold text-navy underline decoration-gold decoration-2 underline-offset-4 transition hover:text-gold-700">
                    {{ __('public.about.title') }}
                    <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('public.partials.pillar-cards')

    @include('public.partials.vision-mission')

    @include('public.partials.coach-cards')

    @include('public.partials.enrolment-steps')

    <div class="bg-navy-50">
        @include('public.partials.contact-block')
    </div>

    @include('public.partials.cta-block')
</x-public-layout>

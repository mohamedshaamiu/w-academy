<x-public-layout :title="__('public.contact.title')">
    <section class="relative isolate overflow-hidden bg-navy text-white">
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-navy-500 to-navy-700"></div>
            <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold sm:text-4xl">{{ __('public.contact.title') }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-navy-100">{{ __('public.contact.lead') }}</p>
        </div>
    </section>

    @include('public.partials.contact-block')

    <div class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <p class="rounded-xl border border-gold-100 bg-gold-50 p-6 text-navy-700">
            {{ __('public.home.enrolment_notice') }}
        </p>
    </div>
</x-public-layout>

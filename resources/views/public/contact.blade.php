<x-public-layout :title="__('public.contact.title')">
    <section class="relative isolate overflow-hidden bg-navy text-white">
        @include('public.partials.page-hero-backdrop', ['photo' => 'match-teams'])

        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold sm:text-4xl">{{ __('public.contact.title') }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-navy-50">{{ __('public.contact.lead') }}</p>
        </div>
    </section>

    @include('public.partials.contact-block')

    <div class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <p class="rounded-xl border border-gold-100 bg-gold-50 p-6 text-navy-700">
            {{ __('public.home.enrolment_notice') }}
        </p>
    </div>
</x-public-layout>

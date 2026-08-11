<x-public-layout>
    <section class="bg-navy text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="max-w-2xl text-3xl font-bold sm:text-4xl">{{ __('common.app_name') }}</h1>
            <p class="mt-4 max-w-xl text-navy-100">{{ __('common.tagline') }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-navy">{{ __('framework.page.pillars_heading') }}</h2>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($pillars as $pillar)
                <div class="rounded-xl border border-navy-100 p-5 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gold-50 text-gold-700">
                        {{ strtoupper(substr($pillar->translated('name'), 0, 1)) }}
                    </div>
                    <h3 class="font-semibold text-navy">{{ $pillar->translated('name') }}</h3>
                    @if ($pillar->isTranslationFallback('name'))
                        <p class="mt-1 text-xs text-navy-300">{{ __('common.not_available_in_language') }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <a href="{{ route('framework') }}" class="font-semibold text-navy underline hover:text-gold-700">
                {{ __('framework.page.title') }} &raquo;
            </a>
        </div>
    </section>

    <section class="bg-navy-50 py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <p class="rounded-lg bg-white p-6 text-center text-navy-500 shadow-sm">
                {{ __('public.home.enrolment_notice') }}
            </p>
        </div>
    </section>

    @include('public.partials.contact-block')
</x-public-layout>

@php
    // Icons for the three portal views. Kept beside the copy they illustrate
    // rather than in the lang files, which hold text only.
    $portals = [
        'guardian' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
        'student' => 'M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342',
        'coach' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    ];
@endphp

<x-public-layout :title="__('public.about.title')">
    <section class="relative isolate overflow-hidden bg-navy text-white">
        @include('public.partials.page-hero-backdrop', ['photo' => 'academy-group'])

        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <h1 class="max-w-3xl text-3xl font-bold sm:text-4xl">{{ __('public.about.title') }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-navy-50">{{ __('public.about.lead') }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-xl font-bold text-navy">{{ __('public.about.story_heading') }}</h2>
                <p class="mt-4 leading-relaxed text-navy-500">{{ __('public.about.story_body') }}</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-navy">{{ __('public.about.who_heading') }}</h2>
                <p class="mt-4 leading-relaxed text-navy-500">{{ __('public.about.who_body') }}</p>
            </div>
        </div>

        <x-site-photo
            name="beach-squad"
            sizes="(min-width: 640px) 90vw, 100vw"
            class="mt-12 block overflow-hidden rounded-2xl shadow-md ring-1 ring-navy-100"
            img-class="aspect-[16/9] w-full object-cover object-center"
        />
    </section>

    @include('public.partials.vision-mission')

    @include('public.partials.acronym-values')

    <section class="bg-navy-50 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <h2 class="text-2xl font-bold text-navy sm:text-3xl">{{ __('public.about.framework_heading') }}</h2>
                    <p class="mt-4 leading-relaxed text-navy-500">{{ __('public.about.framework_body') }}</p>

                    <a href="{{ route('framework') }}" class="mt-6 inline-flex items-center gap-2 font-semibold text-navy underline decoration-gold decoration-2 underline-offset-4 transition hover:text-gold-700">
                        {{ __('public.pillars.cta') }}
                        <svg class="mirror-inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>

                <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:col-span-7">
                    @foreach ($pillars as $pillar)
                        <li class="flex items-start gap-4 rounded-2xl border border-navy-100 bg-white p-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-navy text-gold">
                                <x-pillar-icon :icon="$pillar->icon" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block font-semibold text-navy">{{ $pillar->translated('name') }}</span>
                                <span class="mt-1 block text-sm leading-relaxed text-navy-500">{{ $pillar->translated('description') }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <h2 class="text-2xl font-bold text-navy sm:text-3xl">{{ __('public.about.portal_heading') }}</h2>
            <p class="mt-3 leading-relaxed text-navy-500">{{ __('public.about.portal_body') }}</p>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
            @foreach ($portals as $key => $path)
                <article class="rounded-2xl border border-navy-100 bg-white p-6">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-50 text-gold-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-navy">{{ __("public.about.portal.{$key}.title") }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-navy-500">{{ __("public.about.portal.{$key}.body") }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-10 rounded-2xl border border-navy-100 bg-navy-50 p-8">
            <h3 class="text-lg font-bold text-navy">{{ __('public.about.language_heading') }}</h3>
            <p class="mt-3 max-w-3xl leading-relaxed text-navy-500">{{ __('public.about.language_body') }}</p>
        </div>
    </section>

    @include('public.partials.gallery-band')

    @include('public.partials.cta-block')
</x-public-layout>

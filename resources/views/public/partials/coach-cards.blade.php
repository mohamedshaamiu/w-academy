{{--
    The academy's coaching staff, read from the coach records the admin
    maintains. Photos are optional — a coach without one gets a crest-palette
    initial instead, so the section never shows a broken image (the same
    no-onerror rule as <x-student-photo>).

    Expects: $coaches (may be empty — the section then renders nothing)
--}}
@if ($coaches->isNotEmpty())
    <section class="bg-navy-50">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-bold text-navy sm:text-3xl">{{ __('public.coaches.heading') }}</h2>
                <p class="mt-3 leading-relaxed text-navy-500">{{ __('public.coaches.body') }}</p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($coaches as $coach)
                    <article class="rounded-2xl border border-navy-100 bg-white p-6 text-center">
                        @if ($coach->hasPhoto())
                            <img
                                src="{{ $coach->photoUrl() }}"
                                alt="{{ $coach->user->name }}"
                                class="mx-auto h-24 w-24 rounded-full object-cover ring-2 ring-gold/60"
                            >
                        @else
                            <span class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-navy text-3xl font-bold text-gold" aria-hidden="true">
                                {{ mb_substr($coach->user->name, 0, 1) }}
                            </span>
                        @endif

                        <h3 class="mt-4 font-semibold text-navy">{{ $coach->user->name }}</h3>
                        <p class="mt-1 text-sm text-navy-500">{{ $coach->specialisation ?: __('public.coaches.role_fallback') }}</p>
                        <p class="mt-1 text-xs text-navy-300">{{ __('public.coaches.since', ['year' => $coach->joined_on->year]) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

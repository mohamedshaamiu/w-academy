{{--
    The W-ACADEMY acronym — eight values, one per letter of the academy's
    name. Driven entirely by `public.values.items`, so the count and order
    come from the lang file rather than from this template.

    The letter is inside the heading rather than beside it: it is part of the
    value's name, and in Dhivehi it is a whole word ("ޑަބްލިއު") rather than a
    single glyph, so it has to read as text to a screen reader in both
    languages. The badge is sized to grow with it.
--}}
<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-navy sm:text-3xl">{{ __('public.values.heading') }}</h2>

    <ul class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
        @foreach (__('public.values.items') as $value)
            <li class="rounded-2xl border border-navy-100 bg-white p-6">
                <h3 class="flex items-center gap-4 font-semibold text-navy">
                    <span class="inline-flex h-11 min-w-[2.75rem] shrink-0 items-center justify-center rounded-xl bg-navy px-3 text-lg font-bold text-gold">{{ $value['letter'] }}</span>
                    <span>{{ $value['title'] }}</span>
                </h3>
                <p class="mt-3 text-sm leading-relaxed text-navy-500">{{ $value['body'] }}</p>
            </li>
        @endforeach
    </ul>
</section>

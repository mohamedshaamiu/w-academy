@php
    // SPEC.md §9 "Public 3": the contact page is static. The academy supplied
    // all four values on 22 Aug 2026 — see lang/{dv,en}/public.php.
    //
    // Phone and email carry an href and dir="ltr": they are Latin digits and
    // ASCII inside an RTL block, so without it the bidi algorithm reorders
    // them. The <a> is inline, so the dir does not disturb the mirrored
    // alignment of the card.
    $details = [
        [
            'heading' => __('public.contact.address_heading'),
            'value' => __('public.contact.address_value'),
            'path' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        ],
        [
            'heading' => __('public.contact.phone_heading'),
            'value' => __('public.contact.phone_value'),
            'href' => 'tel:'.preg_replace('/\D/', '', __('public.contact.phone_value')),
            'path' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z',
        ],
        [
            'heading' => __('public.contact.email_heading'),
            'value' => __('public.contact.email_value'),
            'href' => 'mailto:'.__('public.contact.email_value'),
            'path' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        ],
        [
            'heading' => __('public.contact.office_hours_heading'),
            'value' => __('public.contact.office_hours_value'),
            'path' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];
@endphp

<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="max-w-2xl">
        <h2 class="text-2xl font-bold text-navy sm:text-3xl">{{ __('public.contact.title') }}</h2>
        <p class="mt-3 leading-relaxed text-navy-500">{{ __('public.contact.lead') }}</p>
    </div>

    <dl class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($details as $detail)
            <div class="rounded-2xl border border-navy-100 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $detail['path'] }}" />
                    </svg>
                </div>
                <dt class="mt-4 text-sm font-semibold text-navy-400">{{ $detail['heading'] }}</dt>
                <dd class="mt-1 font-medium text-navy-700">
                    @isset($detail['href'])
                        <a href="{{ $detail['href'] }}" dir="ltr" class="underline decoration-gold decoration-2 underline-offset-4 transition hover:text-gold-700">{{ $detail['value'] }}</a>
                    @else
                        {{ $detail['value'] }}
                    @endisset
                </dd>
            </div>
        @endforeach
    </dl>
</section>

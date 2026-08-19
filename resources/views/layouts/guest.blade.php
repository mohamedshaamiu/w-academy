<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('common.app_name') }}</title>

        @include('partials.favicons')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{-- SPEC.md §3.4: the Thaana face applies only in the dv locale. --}}
    <body class="{{ app()->getLocale() === 'dv' ? 'font-thaana' : 'font-sans' }} antialiased bg-navy-900 text-navy-900">
        {{-- The academy's own photograph behind the sign-in card. It is fixed
             and scrimmed: the card and the wordmark sit on top of it, so the
             scrim is what keeps white text legible, not decoration. --}}
        <div class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
            <x-site-photo
                name="training-stretch"
                :priority="true"
                sizes="100vw"
                class="block h-full w-full"
                img-class="h-full w-full object-cover object-center"
            />
            <div class="absolute inset-0 bg-navy-900/85"></div>
        </div>

        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <div class="mb-6 flex items-center gap-2 text-xl font-bold text-white">
                <img src="{{ asset('images/crest.png') }}" alt="" class="h-12 w-12 rounded-full ring-1 ring-gold/60">
                {{ __('common.app_name') }}
            </div>

            <div class="w-full {{ $maxWidth ?? 'max-w-sm' }} rounded-xl bg-white p-6 shadow-2xl">
                {{ $slot }}
            </div>

            <div class="mt-6">
                <x-lang-switcher />
            </div>
        </div>
    </body>
</html>

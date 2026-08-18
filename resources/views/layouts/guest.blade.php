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
    <body class="{{ app()->getLocale() === 'dv' ? 'font-thaana' : 'font-sans' }} antialiased bg-navy-50 text-navy-900">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <div class="mb-6 flex items-center gap-2 text-xl font-bold text-navy">
                <img src="{{ asset('images/crest.png') }}" alt="" class="h-12 w-12 rounded-full">
                {{ __('common.app_name') }}
            </div>

            <div class="w-full {{ $maxWidth ?? 'max-w-sm' }} rounded-xl bg-white p-6 shadow-md">
                {{ $slot }}
            </div>

            <div class="mt-6">
                <x-lang-switcher />
            </div>
        </div>
    </body>
</html>

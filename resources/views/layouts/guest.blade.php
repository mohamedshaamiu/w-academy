<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('common.app_name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-thaana antialiased bg-navy-50 text-navy-900">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <div class="mb-6 flex items-center gap-2 text-xl font-bold text-navy">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gold text-navy-900">W</span>
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

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('common.app_name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{-- SPEC.md §3.4: the Thaana face applies only in the dv locale. --}}
    <body class="{{ app()->getLocale() === 'dv' ? 'font-thaana' : 'font-sans' }} antialiased bg-white text-navy-900">
        <header class="bg-navy text-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gold text-navy-900">W</span>
                    {{ __('common.app_name') }}
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium sm:flex">
                    <a href="{{ route('home') }}" class="hover:text-gold">{{ __('nav.home') }}</a>
                    <a href="{{ route('framework') }}" class="hover:text-gold">{{ __('nav.framework') }}</a>
                    <a href="{{ route('contact') }}" class="hover:text-gold">{{ __('nav.contact') }}</a>
                    <a href="{{ route('login') }}" class="rounded-md bg-gold px-4 py-2 text-navy-900 hover:bg-gold-400">{{ __('nav.login') }}</a>
                </nav>

                <div class="flex items-center gap-3">
                    <x-lang-switcher />
                </div>
            </div>

            <nav class="flex items-center gap-4 overflow-x-auto border-t border-white/10 px-4 py-2 text-sm font-medium sm:hidden">
                <a href="{{ route('home') }}" class="hover:text-gold">{{ __('nav.home') }}</a>
                <a href="{{ route('framework') }}" class="hover:text-gold">{{ __('nav.framework') }}</a>
                <a href="{{ route('contact') }}" class="hover:text-gold">{{ __('nav.contact') }}</a>
                <a href="{{ route('login') }}" class="hover:text-gold">{{ __('nav.login') }}</a>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-16 border-t border-navy-100 bg-navy-50 py-8 text-center text-sm text-navy-500">
            &copy; {{ now()->year }} {{ __('common.app_name') }}
        </footer>
    </body>
</html>

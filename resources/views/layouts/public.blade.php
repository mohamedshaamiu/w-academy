@php
    // One definition drives the header, the mobile bar and the footer sitemap.
    $publicNav = [
        'home' => __('nav.home'),
        'about' => __('nav.about'),
        'framework' => __('nav.framework'),
        'contact' => __('nav.contact'),
    ];
@endphp

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
        <header class="sticky top-0 z-40 bg-navy text-white shadow-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gold text-navy-900">W</span>
                    {{ __('common.app_name') }}
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium sm:flex">
                    @foreach ($publicNav as $route => $label)
                        <a
                            href="{{ route($route) }}"
                            @class([
                                'transition hover:text-gold',
                                'text-gold' => request()->routeIs($route),
                            ])
                            @if (request()->routeIs($route)) aria-current="page" @endif
                        >{{ $label }}</a>
                    @endforeach

                    <a href="{{ route('login') }}" class="rounded-md bg-gold px-4 py-2 text-navy-900 transition hover:bg-gold-400">{{ __('nav.login') }}</a>
                </nav>

                <div class="flex items-center gap-3">
                    <x-lang-switcher />
                </div>
            </div>

            <nav class="flex items-center gap-4 overflow-x-auto border-t border-white/10 px-4 py-2 text-sm font-medium sm:hidden">
                @foreach ($publicNav as $route => $label)
                    <a
                        href="{{ route($route) }}"
                        @class([
                            'whitespace-nowrap transition hover:text-gold',
                            'text-gold' => request()->routeIs($route),
                        ])
                        @if (request()->routeIs($route)) aria-current="page" @endif
                    >{{ $label }}</a>
                @endforeach

                <a href="{{ route('login') }}" class="whitespace-nowrap font-semibold text-gold">{{ __('nav.login') }}</a>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-navy-100 bg-navy-50 text-sm text-navy-500">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-10 px-4 py-12 sm:grid-cols-3 sm:px-6 lg:px-8">
                <div>
                    <p class="flex items-center gap-2 text-base font-bold text-navy">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-navy text-gold">W</span>
                        {{ __('common.app_name') }}
                    </p>
                    <p class="mt-3 leading-relaxed">{{ __('public.footer.blurb') }}</p>
                </div>

                <div>
                    <p class="font-semibold text-navy">{{ __('public.footer.explore_heading') }}</p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($publicNav as $route => $label)
                            <li><a href="{{ route($route) }}" class="transition hover:text-gold-700">{{ $label }}</a></li>
                        @endforeach
                        <li><a href="{{ route('login') }}" class="transition hover:text-gold-700">{{ __('nav.login') }}</a></li>
                    </ul>
                </div>

                <div>
                    <p class="font-semibold text-navy">{{ __('public.footer.reach_heading') }}</p>
                    <dl class="mt-3 space-y-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-navy-300">{{ __('public.contact.address_heading') }}</dt>
                            <dd>{{ __('public.contact.address_value') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-navy-300">{{ __('public.contact.phone_heading') }}</dt>
                            <dd>{{ __('public.contact.phone_value') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-navy-300">{{ __('public.contact.office_hours_heading') }}</dt>
                            <dd>{{ __('public.contact.office_hours_value') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="border-t border-navy-100 py-6 text-center">
                &copy; {{ now()->year }} {{ __('common.app_name') }}
            </div>
        </footer>
    </body>
</html>

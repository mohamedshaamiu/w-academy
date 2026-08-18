@php
    $portal = $portal ?? 'guardian';

    $navLinks = match ($portal) {
        'guardian' => [
            ['route' => 'guardian.dashboard', 'label' => __('nav.guardian.dashboard')],
            ['route' => 'guardian.children.index', 'label' => __('nav.guardian.children')],
            ['route' => 'guardian.schedule', 'label' => __('nav.guardian.schedule')],
            ['route' => 'guardian.profile.edit', 'label' => __('nav.guardian.profile')],
        ],
        'student' => [
            ['route' => 'student.dashboard', 'label' => __('nav.student.dashboard')],
            ['route' => 'student.schedule', 'label' => __('nav.student.schedule')],
            ['route' => 'student.attendance', 'label' => __('nav.student.attendance')],
            ['route' => 'student.profile.edit', 'label' => __('nav.student.profile')],
        ],
        'coach' => [
            ['route' => 'coach.dashboard', 'label' => __('nav.coach.dashboard')],
            ['route' => 'coach.squads.index', 'label' => __('nav.coach.squads')],
            ['route' => 'coach.sessions.index', 'label' => __('nav.coach.sessions')],
        ],
        'admin' => [
            ['route' => 'admin.dashboard', 'label' => __('nav.admin.dashboard')],
            ['route' => 'admin.students.index', 'label' => __('nav.admin.students')],
            ['route' => 'admin.guardians.index', 'label' => __('nav.admin.guardians')],
            ['route' => 'admin.coaches.index', 'label' => __('nav.admin.coaches')],
            ['route' => 'admin.squads.index', 'label' => __('nav.admin.squads')],
            ['route' => 'admin.sessions.index', 'label' => __('nav.admin.sessions')],
            ['route' => 'admin.agreement-templates.index', 'label' => __('nav.admin.agreement_templates')],
            ['route' => 'admin.agreements.index', 'label' => __('nav.admin.agreements')],
            ['route' => 'admin.framework.pillars.index', 'label' => __('nav.admin.framework')],
            ['route' => 'admin.reports.attendance', 'label' => __('nav.admin.reports')],
            ['route' => 'admin.audit.index', 'label' => __('nav.admin.audit')],
        ],
        default => [],
    };

    // Admin carries eleven destinations — more than a top bar holds without
    // scrolling, so from `lg` up it moves to a sidebar. The three narrower
    // portals keep the top nav. Below `lg` every portal uses the strip.
    $sidebar = $portal === 'admin';

    // 'admin.students.index' -> matches anything under 'admin.students*'.
    $isActive = function (string $route): bool {
        [$group, $section] = array_pad(explode('.', $route), 2, '');

        return request()->routeIs($group.'.'.$section.'*');
    };
@endphp
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
        <div class="min-h-screen {{ $sidebar ? 'lg:flex' : '' }}">
            @if ($sidebar)
                {{-- Sits on the start edge, so it mirrors to the right under RTL. --}}
                <aside class="hidden bg-navy text-white lg:flex lg:w-64 lg:shrink-0 lg:flex-col">
                    <a href="{{ route('dashboard.redirect') }}" class="flex items-center gap-2 px-5 py-4 font-bold">
                        <img src="{{ asset('images/crest.png') }}" alt="" class="h-8 w-8 rounded-full ring-1 ring-gold/60">
                        <span>{{ __('common.app_name') }}</span>
                    </a>

                    <nav class="flex flex-1 flex-col gap-0.5 px-3 pb-6 text-sm font-medium">
                        @foreach ($navLinks as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                @class([
                                    'rounded-md border-s-4 px-3 py-2 text-start',
                                    'border-gold bg-white/10 text-gold' => $isActive($link['route']),
                                    'border-transparent hover:bg-white/5 hover:text-gold' => ! $isActive($link['route']),
                                ])
                                @if ($isActive($link['route'])) aria-current="page" @endif
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </aside>
            @endif

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="bg-navy text-white">
                    <div class="mx-auto flex w-full items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8 {{ $sidebar ? '' : 'max-w-7xl' }}">
                        {{-- With a sidebar the wordmark lives there instead, from lg up. --}}
                        <a href="{{ route('dashboard.redirect') }}" class="flex shrink-0 items-center gap-2 font-bold {{ $sidebar ? 'lg:hidden' : '' }}">
                            <img src="{{ asset('images/crest.png') }}" alt="" class="h-8 w-8 rounded-full ring-1 ring-gold/60">
                            <span class="hidden sm:inline">{{ __('common.app_name') }}</span>
                        </a>

                        @unless ($sidebar)
                            <nav class="hidden flex-1 items-center gap-5 overflow-x-auto text-sm font-medium md:flex">
                                @foreach ($navLinks as $link)
                                    <a
                                        href="{{ route($link['route']) }}"
                                        class="whitespace-nowrap border-b-2 py-1 {{ $isActive($link['route']) ? 'border-gold text-gold' : 'border-transparent hover:text-gold' }}"
                                        @if ($isActive($link['route'])) aria-current="page" @endif
                                    >
                                        {{ $link['label'] }}
                                    </a>
                                @endforeach
                            </nav>
                        @endunless

                        <div class="flex items-center gap-3 {{ $sidebar ? 'ms-auto' : '' }}">
                            <x-lang-switcher />
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-md border border-white/30 px-3 py-1.5 text-sm hover:bg-white/10">
                                    {{ __('nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <nav class="flex items-center gap-4 overflow-x-auto border-t border-white/10 px-4 py-2 text-sm font-medium {{ $sidebar ? 'lg:hidden' : 'md:hidden' }}">
                        @foreach ($navLinks as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                class="whitespace-nowrap {{ $isActive($link['route']) ? 'text-gold' : 'hover:text-gold' }}"
                                @if ($isActive($link['route'])) aria-current="page" @endif
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </header>

                @if (session('status'))
                    <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
                    </div>
                @endif

                @if (session('notice'))
                    <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="rounded-md bg-gold-50 px-4 py-3 text-sm text-navy-900">{{ session('notice') }}</div>
                    </div>
                @endif

                @isset($header)
                    <div class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                        <h1 class="text-xl font-bold text-navy">{{ $header }}</h1>
                    </div>
                @endisset

                <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

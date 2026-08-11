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
            ['route' => 'admin.framework.pillars.index', 'label' => __('nav.admin.framework')],
            ['route' => 'admin.reports.attendance', 'label' => __('nav.admin.reports')],
            ['route' => 'admin.audit.index', 'label' => __('nav.admin.audit')],
        ],
        default => [],
    };
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
    <body class="font-thaana antialiased bg-navy-50 text-navy-900">
        <div class="min-h-screen">
            <header class="bg-navy text-white">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ route('dashboard.redirect') }}" class="flex shrink-0 items-center gap-2 font-bold">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gold text-navy-900">W</span>
                        <span class="hidden sm:inline">{{ __('common.app_name') }}</span>
                    </a>

                    <nav class="hidden flex-1 items-center gap-5 overflow-x-auto text-sm font-medium md:flex">
                        @foreach ($navLinks as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                class="whitespace-nowrap border-b-2 py-1 {{ request()->routeIs(explode('.', $link['route'])[0].'.'.explode('.', $link['route'])[1].'*') ? 'border-gold text-gold' : 'border-transparent hover:text-gold' }}"
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-3">
                        <x-lang-switcher />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-white/30 px-3 py-1.5 text-sm hover:bg-white/10">
                                {{ __('nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>

                <nav class="flex items-center gap-4 overflow-x-auto border-t border-white/10 px-4 py-2 text-sm font-medium md:hidden">
                    @foreach ($navLinks as $link)
                        <a href="{{ route($link['route']) }}" class="whitespace-nowrap hover:text-gold">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </header>

            @if (session('status'))
                <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
                </div>
            @endif

            @if (session('notice'))
                <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-md bg-gold-50 px-4 py-3 text-sm text-navy-900">{{ session('notice') }}</div>
                </div>
            @endif

            @isset($header)
                <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    <h1 class="text-xl font-bold text-navy">{{ $header }}</h1>
                </div>
            @endisset

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>

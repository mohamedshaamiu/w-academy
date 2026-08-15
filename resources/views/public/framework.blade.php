<x-public-layout :title="__('framework.page.title')">
    <section class="relative isolate overflow-hidden bg-navy text-white">
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-navy-500 to-navy-700"></div>
            <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold sm:text-4xl">{{ __('framework.page.title') }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-navy-100">{{ __('framework.page.lead') }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-navy-100 bg-navy-50 p-6">
            <h2 class="font-bold text-navy">{{ __('framework.page.how_to_read_heading') }}</h2>
            <p class="mt-2 max-w-3xl leading-relaxed text-navy-500">{{ __('framework.page.how_to_read_body') }}</p>
        </div>

        <section class="mt-12">
            <h2 class="text-xl font-bold text-navy">{{ __('framework.page.pillars_heading') }}</h2>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                @foreach ($pillars as $pillar)
                    <article class="flex items-start gap-4 rounded-2xl border border-navy-100 bg-white p-6">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-navy text-gold">
                            <x-pillar-icon :icon="$pillar->icon" class="h-6 w-6" />
                        </span>

                        <div>
                            <h3 class="font-semibold text-navy">{{ $pillar->translated('name') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-navy-500">{{ $pillar->translated('description') }}</p>
                            @if ($pillar->isTranslationFallback('description'))
                                <p class="mt-1 text-xs text-navy-300">{{ __('common.not_available_in_language') }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-12">
            <h2 class="text-xl font-bold text-navy">{{ __('framework.page.strike_ladder_heading') }}</h2>

            <div class="mt-6 hidden overflow-x-auto rounded-2xl border border-navy-100 sm:block">
                <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.level') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.type') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.action') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.parent_role') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-100 bg-white">
                        @foreach ($strikeLevels as $level)
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gold-50 font-bold text-gold-700">{{ $level->level }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $level->translated('type') }}</td>
                                <td class="px-4 py-3">{{ $level->translated('action') }}</td>
                                <td class="px-4 py-3">{{ $level->translated('parent_role') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 space-y-4 sm:hidden">
                @foreach ($strikeLevels as $level)
                    <div class="rounded-2xl border border-navy-100 bg-white p-5">
                        <p class="flex items-center gap-2 font-bold text-navy">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gold-50 text-gold-700">{{ $level->level }}</span>
                            {{ __('framework.page.table.level') }}
                        </p>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div>
                                <dt class="font-semibold text-navy-400">{{ __('framework.page.table.type') }}</dt>
                                <dd class="text-navy-700">{{ $level->translated('type') }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-navy-400">{{ __('framework.page.table.action') }}</dt>
                                <dd class="text-navy-700">{{ $level->translated('action') }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-navy-400">{{ __('framework.page.table.parent_role') }}</dt>
                                <dd class="text-navy-700">{{ $level->translated('parent_role') }}</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    @include('public.partials.cta-block')
</x-public-layout>

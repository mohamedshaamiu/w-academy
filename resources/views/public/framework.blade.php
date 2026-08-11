<x-public-layout :title="__('framework.page.title')">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-navy">{{ __('framework.page.title') }}</h1>

        <section class="mt-8">
            <h2 class="text-lg font-bold text-navy">{{ __('framework.page.pillars_heading') }}</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($pillars as $pillar)
                    <div class="rounded-xl border border-navy-100 p-5">
                        <h3 class="font-semibold text-navy">{{ $pillar->translated('name') }}</h3>
                        <p class="mt-2 text-sm text-navy-500">{{ $pillar->translated('description') }}</p>
                        @if ($pillar->isTranslationFallback('description'))
                            <p class="mt-1 text-xs text-navy-300">{{ __('common.not_available_in_language') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-12">
            <h2 class="text-lg font-bold text-navy">{{ __('framework.page.strike_ladder_heading') }}</h2>

            <div class="mt-4 hidden overflow-x-auto rounded-xl border border-navy-100 sm:block">
                <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.level') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.type') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.action') }}</th>
                            <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('framework.page.table.parent_role') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-100">
                        @foreach ($strikeLevels as $level)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-navy">{{ $level->level }}</td>
                                <td class="px-4 py-3">{{ $level->translated('type') }}</td>
                                <td class="px-4 py-3">{{ $level->translated('action') }}</td>
                                <td class="px-4 py-3">{{ $level->translated('parent_role') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 space-y-4 sm:hidden">
                @foreach ($strikeLevels as $level)
                    <div class="rounded-xl border border-navy-100 p-4">
                        <p class="font-bold text-navy">{{ __('framework.page.table.level') }} {{ $level->level }}</p>
                        <dl class="mt-2 space-y-2 text-sm">
                            <div>
                                <dt class="font-semibold text-navy-400">{{ __('framework.page.table.type') }}</dt>
                                <dd>{{ $level->translated('type') }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-navy-400">{{ __('framework.page.table.action') }}</dt>
                                <dd>{{ $level->translated('action') }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-navy-400">{{ __('framework.page.table.parent_role') }}</dt>
                                <dd>{{ $level->translated('parent_role') }}</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-public-layout>

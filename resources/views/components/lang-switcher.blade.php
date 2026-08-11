@php
    $current = app()->getLocale();
    $query = request()->query();
@endphp

<div class="inline-flex items-center gap-1 rounded-full bg-navy-800/10 p-1 text-sm">
    @foreach (\App\Enums\Locale::cases() as $locale)
        <a
            href="{{ route('locale.switch', array_merge(['locale' => $locale->value], $query)) }}"
            class="rounded-full px-3 py-1 font-medium transition {{ $current === $locale->value ? 'bg-gold text-navy-900' : 'text-current hover:bg-navy-800/10' }}"
            aria-current="{{ $current === $locale->value ? 'true' : 'false' }}"
        >
            {{ $locale->label() }}
        </a>
    @endforeach
</div>

@props([
    'name',
    'sizes' => '100vw',
    'priority' => false,
    'imgClass' => 'h-full w-full object-cover',
])

@php
    // The single construction site for public photography URLs — the marketing
    // counterpart of Student::photoUrl(). Nothing else builds these paths.
    //
    // Each entry is derived from `client photos/` (see CLAUDE.md § Photography)
    // into 1600w and 800w renditions, WebP with a JPEG fallback. The intrinsic
    // size recorded here is the 1600w rendition; declaring it on the <img> lets
    // the browser reserve the right box before the file arrives, so a slow
    // connection never shifts the page under the reader.
    $renditions = [
        'academy-group' => [1600, 1066],
        'squad-night' => [1600, 899],
        'match-teams' => [1600, 900],
        'beach-squad' => [1600, 1205],
        'beach-line' => [1600, 1205],
        'training-lineup' => [1600, 1205],
        'training-stretch' => [1600, 1200],
    ];

    if (! isset($renditions[$name])) {
        throw new InvalidArgumentException("Unknown site photo [{$name}].");
    }

    [$width, $height] = $renditions[$name];
    $base = asset("images/photos/{$name}");
@endphp

{{--
    `alt` is empty by design, not by omission. Every photo placed through this
    component is illustrative: it sits beside a heading that already carries the
    meaning, so a screen reader gains nothing from a description. That also
    keeps the photography clear of rule 1 — writing alt text would mean
    inventing Dhivehi the customer has not supplied (SPEC.md §3.6), and a
    `[DV CONTENT PENDING]` alt attribute would be worse than none.

    No `onerror` fallback here either: a missing file must be visible.
--}}
<picture {{ $attributes }}>
    <source
        type="image/webp"
        srcset="{{ $base }}-800.webp 800w, {{ $base }}-1600.webp 1600w"
        sizes="{{ $sizes }}"
    >
    <img
        src="{{ $base }}-1600.jpg"
        srcset="{{ $base }}-800.jpg 800w, {{ $base }}-1600.jpg 1600w"
        sizes="{{ $sizes }}"
        width="{{ $width }}"
        height="{{ $height }}"
        alt=""
        @if ($priority) fetchpriority="high" @else loading="lazy" @endif
        decoding="async"
        class="{{ $imgClass }}"
    >
</picture>

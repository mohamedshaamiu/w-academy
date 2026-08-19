@php
    // The academy's own photographs. Six 4:3 tiles and one panorama that spans
    // the full width: that makes the tile count fill its last row exactly at
    // both breakpoints (2 columns and 4), so the band never trails off ragged.
    $gallery = [
        'training-stretch',
        'match-teams',
        'squad-night',
        'beach-squad',
        'training-lineup',
        'academy-group',
    ];
@endphp

{{--
    A wordless band, on purpose. Captions would mean new user-facing copy, and
    Dhivehi copy is the customer's to write rather than this project's
    (SPEC.md §3.6) — so the section is labelled from an existing translated
    string and the photographs speak for themselves.
--}}
<section
    class="bg-navy-900 py-3"
    aria-label="{{ __('public.hero.label') }}"
>
    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
        @foreach ($gallery as $photo)
            <x-site-photo
                :name="$photo"
                sizes="(min-width: 1024px) 25vw, 50vw"
                class="group block overflow-hidden"
                img-class="aspect-[4/3] w-full object-cover object-center transition duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
            />
        @endforeach

        {{-- The sandbar panorama, given the width it was shot for. --}}
        <x-site-photo
            name="beach-line"
            sizes="100vw"
            class="group col-span-2 block overflow-hidden lg:col-span-4"
            img-class="aspect-[16/6] w-full object-cover object-center transition duration-500 group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100"
        />
    </div>
</section>

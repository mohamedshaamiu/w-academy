{{--
    The backdrop shared by the /about, /framework and /contact page heroes.
    Takes one variable, $photo, naming an entry in <x-site-photo>.

    Include it as the first child of a `relative isolate overflow-hidden`
    section that renders white copy — the navy scrim here is what makes that
    copy legible over a photograph, so it is load-bearing, not decoration.
--}}
<div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
    <x-site-photo
        :name="$photo"
        sizes="100vw"
        class="block h-full w-full"
        img-class="h-full w-full object-cover object-center"
    />

    {{-- Vertical, so it reads the same under rtl and ltr without mirroring. --}}
    <div class="absolute inset-0 bg-gradient-to-b from-navy-900/85 to-navy-900/90"></div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
</div>

{{--
    Shared by the home page and /about. The photograph sits behind both cards
    as a band rather than inside either one, so the section reads as one moment
    on the pitch with the words laid over it.

    The cards stay opaque white: the copy inside them is body text, and body
    text over a photograph is a contrast risk that no scrim reliably fixes.
--}}
<section class="relative isolate overflow-hidden py-16">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <x-site-photo
            name="match-teams"
            sizes="100vw"
            class="block h-full w-full"
            img-class="h-full w-full object-cover object-center"
        />
        <div class="absolute inset-0 bg-navy-900/80"></div>
    </div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <article class="relative rounded-2xl border border-navy-100 bg-white p-8 shadow-lg">
                <span class="absolute inset-y-8 start-0 w-1 rounded-full bg-gold" aria-hidden="true"></span>
                <h2 class="text-xl font-bold text-navy">{{ __('public.vision.heading') }}</h2>
                <p class="mt-4 leading-relaxed text-navy-500">{{ __('public.vision.body') }}</p>
            </article>

            <article class="relative rounded-2xl border border-navy-100 bg-white p-8 shadow-lg">
                <span class="absolute inset-y-8 start-0 w-1 rounded-full bg-navy" aria-hidden="true"></span>
                <h2 class="text-xl font-bold text-navy">{{ __('public.mission.heading') }}</h2>
                <p class="mt-4 leading-relaxed text-navy-500">{{ __('public.mission.body') }}</p>
            </article>
        </div>
    </div>
</section>

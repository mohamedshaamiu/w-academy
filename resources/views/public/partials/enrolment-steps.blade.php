@php
    $steps = array_values(__('public.how.steps'));
@endphp

{{--
    SPEC.md §9 "Public 1": the home page carries a translated note that
    enrolment is arranged through the academy office. There is no registration
    form and no sign-up link anywhere on this page — the steps describe an
    in-person process on purpose.
--}}
<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-12">
        <div class="lg:col-span-7">
            <h2 class="text-2xl font-bold text-navy sm:text-3xl">{{ __('public.how.heading') }}</h2>
            <p class="mt-3 leading-relaxed text-navy-500">{{ __('public.how.body') }}</p>
        </div>

        <div class="lg:col-span-5">
            <x-site-photo
                name="academy-group"
                sizes="(min-width: 1024px) 40vw, 100vw"
                class="block overflow-hidden rounded-2xl shadow-md ring-1 ring-navy-100"
                img-class="aspect-[3/2] w-full object-cover"
            />
        </div>
    </div>

    <ol class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($steps as $step)
            <li class="relative ps-6">
                <span class="absolute inset-y-0 start-0 w-px bg-navy-100" aria-hidden="true"></span>
                <span class="absolute start-0 top-1 h-6 w-px bg-gold" aria-hidden="true"></span>

                <span class="text-sm font-bold text-gold-700">{{ $loop->iteration }}</span>
                <h3 class="mt-1 font-semibold text-navy">{{ $step['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-navy-500">{{ $step['body'] }}</p>
            </li>
        @endforeach
    </ol>

    <p class="mt-10 rounded-xl border border-gold-100 bg-gold-50 p-6 text-navy-700">
        {{ __('public.home.enrolment_notice') }}
    </p>
</section>

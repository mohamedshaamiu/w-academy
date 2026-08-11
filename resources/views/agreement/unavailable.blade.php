{{--
    Shown when the agreement gate fails closed because no template is current
    (SPEC.md §8.3). Guardians and students land here; admins never do, so they
    can still publish a template.
--}}
<x-guest-layout>
    <h1 class="mb-2 text-center text-lg font-bold text-navy">{{ __('agreement.unavailable.title') }}</h1>
    <p class="text-center text-sm text-navy-400">{{ __('agreement.unavailable.body') }}</p>
</x-guest-layout>

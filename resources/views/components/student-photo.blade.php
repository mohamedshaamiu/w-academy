{{--
    The single shared construction site for student photo URLs (SPEC.md §8.6).
    The URL is signed by Student::photoUrl(); the route additionally enforces
    `auth` and StudentPolicy@viewPhoto.

    When no photo is available the placeholder below is rendered instead. It is
    deliberately visible and distinct from a working photo — media failures are
    never suppressed with `onerror`.
--}}
@props(['student'])

@if ($student->hasPhoto())
    <img
        src="{{ $student->photoUrl() }}"
        alt="{{ __('student.fields.photo') }}"
        {{ $attributes->merge(['class' => 'rounded-full bg-navy-50 object-cover']) }}
    >
@else
    <span
        data-student-photo-placeholder
        role="img"
        aria-label="{{ __('student.fields.photo') }}"
        title="{{ __('student.fields.photo') }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full border border-dashed border-navy-300 bg-navy-100 text-sm font-semibold uppercase text-navy-400']) }}
    >{{ mb_substr(trim($student->full_name), 0, 1) }}</span>
@endif

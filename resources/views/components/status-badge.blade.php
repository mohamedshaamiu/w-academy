@props(['status', 'label'])

@php
    $colors = [
        'pending' => 'bg-amber-100 text-amber-800',
        'active' => 'bg-green-100 text-green-800',
        'suspended' => 'bg-red-100 text-red-800',
        'inactive' => 'bg-navy-100 text-navy-600',
        'planned' => 'bg-navy-100 text-navy-600',
        'in_progress' => 'bg-amber-100 text-amber-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'present' => 'bg-green-100 text-green-800',
        'absent' => 'bg-red-100 text-red-800',
        'late' => 'bg-amber-100 text-amber-800',
        'excused' => 'bg-navy-100 text-navy-600',
        'signed' => 'bg-green-100 text-green-800',
        'revoked' => 'bg-red-100 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold '.($colors[$status] ?? 'bg-navy-100 text-navy-600')]) }}>
    {{ $label }}
</span>

@props(['label', 'value', 'color' => 'slate'])

@php
    $colors = [
        'slate'   => 'text-slate-900',
        'indigo'  => 'text-brand-600',
        'amber'   => 'text-amber-600',
        'emerald' => 'text-emerald-600',
        'rose'    => 'text-rose-600',
    ];
    $colorClass = $colors[$color] ?? $colors['slate'];
@endphp

<div class="card p-5">
    <div class="text-3xl font-bold {{ $colorClass }}">{{ $value }}</div>
    <div class="text-sm text-slate-500 mt-1">{{ $label }}</div>
</div>
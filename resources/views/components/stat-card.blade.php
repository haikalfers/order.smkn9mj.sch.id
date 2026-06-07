@props(['label', 'value', 'icon', 'color' => 'brand', 'sub' => null])

@php
$colors = [
    'brand'  => 'bg-brand-50 text-brand-600',
    'amber'  => 'bg-amber-50 text-amber-600',
    'green'  => 'bg-green-50 text-green-600',
    'purple' => 'bg-purple-50 text-purple-600',
    'red'    => 'bg-red-50 text-red-600',
    'indigo' => 'bg-indigo-50 text-indigo-600',
];
$iconClass = $colors[$color] ?? $colors['brand'];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-5 flex items-start gap-4">
    <div class="w-11 h-11 rounded-xl {{ $iconClass }} flex items-center justify-center shrink-0">
        {!! $icon !!}
    </div>
    <div>
        <p class="text-slate-500 text-xs font-medium">{{ $label }}</p>
        <p class="text-slate-900 text-2xl font-extrabold mt-0.5 leading-tight">{{ $value }}</p>
        @if($sub)
        <p class="text-slate-400 text-xs mt-0.5">{{ $sub }}</p>
        @endif
    </div>
</div>

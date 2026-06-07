@props(['status'])

@php
$map = [
    'pending'        => ['label' => 'Pending',       'class' => 'bg-amber-100 text-amber-800'],
    'design_process' => ['label' => 'Proses Desain',  'class' => 'bg-blue-100 text-blue-800'],
    'design_done'    => ['label' => 'Desain Selesai', 'class' => 'bg-indigo-100 text-indigo-800'],
    'production'     => ['label' => 'Produksi',       'class' => 'bg-purple-100 text-purple-800'],
    'done'           => ['label' => 'Selesai',        'class' => 'bg-green-100 text-green-800'],
    'delivered'      => ['label' => 'Terkirim',       'class' => 'bg-teal-100 text-teal-800'],
    'cancelled'      => ['label' => 'Dibatalkan',     'class' => 'bg-red-100 text-red-800'],
];
$info = $map[$status] ?? ['label' => $status, 'class' => 'bg-slate-100 text-slate-700'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $info['class'] }}">
    {{ $info['label'] }}
</span>

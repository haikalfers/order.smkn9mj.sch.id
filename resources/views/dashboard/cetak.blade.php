@extends('layouts.app')
@section('title', 'Dashboard Produksi')
@section('page-title', 'Dashboard Produksi')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-2 gap-4">
        <x-stat-card
            label="Task Produksi Aktif"
            :value="($activeTasks ?? collect())->count()"
            color="purple"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>'
        />
        <x-stat-card
            label="Siap Cetak"
            :value="($readyToPrint ?? collect())->count()"
            color="indigo"
            :sub="'Desain sudah selesai'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>'
        />
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Active Production Tasks --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Task Produksi Aktif</h3>
                <a href="{{ route('production-tasks.index') }}" class="text-brand-600 text-sm font-medium hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($activeTasks ?? [] as $task)
                <div class="px-5 py-4 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $task->order->order_number }}</p>
                        <p class="text-xs text-slate-500">{{ $task->order->customer->name }}</p>
                        @if($task->order->deadline)
                        <p class="text-xs {{ $task->order->deadline->isPast() ? 'text-red-500' : 'text-slate-400' }} mt-0.5">
                            Deadline: {{ $task->order->deadline->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                    <a href="{{ route('production-tasks.show', $task) }}" class="text-xs bg-purple-50 text-purple-700 px-3 py-1.5 rounded-lg font-semibold hover:bg-purple-100 transition-colors shrink-0">
                        Update
                    </a>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">Tidak ada task produksi aktif.</div>
                @endforelse
            </div>
        </div>

        {{-- Ready to Print --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Antrian Siap Cetak</h3>
                <p class="text-xs text-slate-500 mt-0.5">Order dengan desain selesai, menunggu produksi</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($readyToPrint ?? [] as $order)
                <div class="px-5 py-4 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-slate-800 hover:text-brand-600">
                            {{ $order->order_number }}
                        </a>
                        <p class="text-xs text-slate-500">{{ $order->customer->name }}</p>
                        <p class="text-xs text-indigo-600 font-medium mt-0.5">Desain Selesai ✓</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">Tidak ada antrian cetak.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

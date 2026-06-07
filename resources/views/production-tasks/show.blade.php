@extends('layouts.app')
@section('title', 'Detail Tugas Produksi')
@section('page-title', 'Tugas Produksi')
@section('breadcrumb')
    <a href="{{ route('production-tasks.index') }}" class="hover:text-brand-600">Tugas Produksi</a> / {{ $task->order->order_number }}
@endsection

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Task Info --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            @php
            $sc = [
                'waiting' => 'bg-slate-100 text-slate-700',
                'in_progress' => 'bg-purple-100 text-purple-800',
                'done' => 'bg-green-100 text-green-800'
            ];
            $sl = [
                'waiting' => 'Menunggu',
                'in_progress' => 'Sedang Produksi',
                'done' => 'Selesai'
            ];
            @endphp
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Informasi Task</h3>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $sc[$task->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $sl[$task->status] ?? $task->status }}
                </span>
            </div>
            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Order</dt>
                    <dd><a href="{{ route('orders.show', $task->order) }}" class="font-bold text-brand-600 hover:underline font-mono">{{ $task->order->order_number }}</a></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pelanggan</dt>
                    <dd class="font-medium text-slate-800">{{ $task->order->customer->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Operator</dt>
                    <dd class="font-medium text-slate-800">{{ $task->assignedUser?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Deadline Order</dt>
                    <dd class="{{ $task->order->deadline?->isPast() ? 'text-red-500 font-semibold' : 'font-medium text-slate-800' }}">
                        {{ $task->order->deadline?->format('d M Y') ?? '—' }}
                    </dd>
                </div>
                @if($task->started_at)
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tanggal Mulai</dt>
                    <dd class="font-medium text-slate-800">{{ $task->started_at->format('d M Y H:i') }}</dd>
                </div>
                @endif
                @if($task->finished_at)
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tanggal Selesai</dt>
                    <dd class="font-medium text-green-700">{{ $task->finished_at->format('d M Y H:i') }}</dd>
                </div>
                @endif
                @if($task->notes)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Catatan</dt>
                    <dd class="text-slate-600">{{ $task->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Item yang Diproduksi</h3>
            </div>
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-2.5 font-semibold">Produk</th>
                    <th class="text-left px-5 py-2.5 font-semibold">Spesifikasi</th>
                    <th class="text-right px-5 py-2.5 font-semibold">Qty</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($task->order->items as $item)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $item->product_name }}</td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ is_array($item->specifications) ? implode(', ', $item->specifications) : ($item->specifications ?? '—') }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ $item->quantity }} {{ $item->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar Actions --}}
    <div class="space-y-5">
        @if(in_array(auth()->user()->role, ['super_admin','admin','cetak']) && $task->status !== 'done')
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">Update Status Produksi</h3>
            <form action="{{ route('production-tasks.update', $task) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                        @foreach(['waiting' => 'Menunggu', 'in_progress' => 'Sedang Produksi', 'done' => 'Selesai'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $task->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-amber-600 mt-1">⚠ Tandai "Selesai" untuk memindahkan order ke status Selesai.</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none resize-none"
                              placeholder="Opsional…">{{ $task->notes }}</textarea>
                </div>
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    Simpan Update
                </button>
            </form>
        </div>
        @endif

        <a href="{{ route('orders.show', $task->order) }}"
           class="flex items-center gap-2 bg-white rounded-xl border border-slate-200 px-5 py-4 text-sm font-semibold text-brand-600 hover:bg-brand-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Lihat Detail Order
        </a>
    </div>
</div>
@endsection

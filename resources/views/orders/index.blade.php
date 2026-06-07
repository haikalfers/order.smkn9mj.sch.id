@extends('layouts.app')
@section('title', 'Daftar Pesanan')
@section('page-title', 'Pesanan')
@section('breadcrumb', 'Kelola semua pesanan masuk')

@section('header-actions')
@if(in_array(auth()->user()->role, ['super_admin','admin']))
<a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Buat Pesanan
</a>
@endif
@endsection

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form action="{{ route('orders.index') }}" method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nomor order / pelanggan…"
                   class="flex-1 min-w-48 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">

            <select name="status" class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                <option value="">Semua Status</option>
                @foreach(['pending','design_process','design_done','production','done','delivered','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $s)) }}
                </option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none">

            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search','status','date_from','date_to']))
            <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide border-b border-slate-200">
                        <th class="text-left px-5 py-3 font-semibold">No. Order</th>
                        <th class="text-left px-5 py-3 font-semibold">Pelanggan</th>
                        <th class="text-left px-5 py-3 font-semibold">Item</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-left px-5 py-3 font-semibold">Deadline</th>
                        <th class="text-left px-5 py-3 font-semibold">Total</th>
                        <th class="text-left px-5 py-3 font-semibold">Pembayaran</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('orders.show', $order) }}" class="font-mono text-xs font-bold text-brand-600 hover:underline">
                                {{ $order->order_number }}
                            </a>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800">{{ $order->customer->name }}</p>
                            <p class="text-xs text-slate-400">{{ $order->customer->phone }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">
                            {{ $order->items_count ?? $order->items->count() }} item
                        </td>
                        <td class="px-5 py-3.5">
                            <x-status-badge :status="$order->status"/>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($order->deadline)
                            <span class="text-xs {{ $order->deadline->isPast() && !in_array($order->status, ['done','delivered','cancelled']) ? 'text-red-500 font-semibold' : 'text-slate-500' }}">
                                {{ $order->deadline->format('d M Y') }}
                            </span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-700">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                            $payBadge = [
                                'unpaid'       => 'bg-red-100 text-red-700',
                                'dp'           => 'bg-amber-100 text-amber-700',
                                'paid'         => 'bg-green-100 text-green-700',
                            ][$order->payment_status] ?? 'bg-slate-100 text-slate-600';
                            $payLabel = ['unpaid' => 'Belum Bayar', 'dp' => 'DP', 'paid' => 'Lunas'][$order->payment_status] ?? $order->payment_status;
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $payBadge }}">{{ $payLabel }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('orders.show', $order) }}" class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if(in_array($order->status, ['pending','design_process']) && in_array(auth()->user()->role, ['super_admin','admin']))
                                <a href="{{ route('orders.edit', $order) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endif
                                <a href="{{ route('orders.print', $order) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Cetak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-slate-400">Belum ada pesanan ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

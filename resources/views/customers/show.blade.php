@extends('layouts.app')
@section('title', $customer->name)
@section('page-title', 'Detail Pelanggan')
@section('breadcrumb')
    <a href="{{ route('customers.index') }}" class="hover:text-brand-600">Pelanggan</a> / {{ $customer->name }}
@endsection

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus pelanggan {{ $customer->name }}? Tindakan ini tidak dapat dibatalkan.')">
        @csrf @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Hapus
        </button>
    </form>
</div>
@endsection

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Contact Info --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 mb-4">Informasi Kontak</h3>
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama</dt>
                    <dd class="mt-1 text-lg font-semibold text-slate-800">{{ $customer->name }}</dd>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Telepon / WA</dt>
                        <dd class="mt-1 text-slate-700">
                            @if($customer->phone)
                                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $customer->phone) }}" target="_blank" class="text-brand-600 hover:underline font-medium">
                                    {{ $customer->phone }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</dt>
                        <dd class="mt-1 text-slate-700">
                            @if($customer->email)
                                <a href="mailto:{{ $customer->email }}" class="text-brand-600 hover:underline font-medium">
                                    {{ $customer->email }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>
                </div>
                @if($customer->address)
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Alamat</dt>
                    <dd class="mt-1 text-slate-700 leading-relaxed">{{ $customer->address }}</dd>
                </div>
                @endif
                @if($customer->notes)
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Catatan</dt>
                    <dd class="mt-1 text-slate-700 text-sm leading-relaxed">{{ $customer->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Orders --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Riwayat Pesanan</h3>
            </div>
            @forelse($orders as $order)
            <div class="border-b border-slate-100 last:border-b-0">
                <a href="{{ route('orders.show', $order) }}" class="block px-6 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $order->order_number }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <x-status-badge :status="$order->status" />
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-600">
                            @php
                            $itemCount = $order->items->count();
                            @endphp
                            {{ $itemCount }} item{{ $itemCount !== 1 ? 's' : '' }}
                        </p>
                        <p class="text-sm font-semibold text-slate-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </a>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-slate-400">
                <p class="text-sm">Belum ada pesanan.</p>
            </div>
            @endforelse

            @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar: Stats --}}
    <div class="space-y-5">
        {{-- Stats Cards --}}
        <div class="grid gap-3">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Pesanan</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Pesanan Selesai</p>
                <p class="text-3xl font-extrabold text-brand-600">{{ $stats['done'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Revenue</p>
                <p class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-3">Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Dibuat</dt>
                    <dd class="text-slate-700 font-medium">{{ $customer->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Diperbarui</dt>
                    <dd class="text-slate-700 font-medium">{{ $customer->updated_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection

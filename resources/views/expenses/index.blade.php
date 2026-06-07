@extends('layouts.app')
@section('title', 'Pengeluaran')
@section('page-title', 'Pengeluaran')
@section('breadcrumb', 'Rekap seluruh pengeluaran per order')

@section('content')
<div class="space-y-4">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            label="Total Bulan Ini"
            :value="'Rp ' . number_format($totalThisMonth ?? 0, 0, ',', '.')"
            color="amber"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            label="Total Keseluruhan"
            :value="'Rp ' . number_format($totalAll ?? 0, 0, ',', '.')"
            color="brand"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 14h.01M15 14h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        />
        <x-stat-card
            label="Jumlah Transaksi"
            :value="$totalCount ?? 0"
            color="indigo"
            :sub="'Bulan ini'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        />
        <x-stat-card
            label="Kategori Terbesar"
            :value="$topCategory ?? '—'"
            color="purple"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'
        />
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form action="{{ route('expenses.index') }}" method="GET" class="flex flex-wrap gap-3">
            <select name="category" class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                <option value="">Semua Kategori</option>
                @foreach(['bahan_baku','operasional','transportasi','lainnya'] as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_',' ',$cat)) }}
                </option>
                @endforeach
            </select>
            <input type="month" name="month" value="{{ request('month') }}"
                   class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">Filter</button>
            @if(request()->hasAny(['category','month']))
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">Reset</a>
            @endif
        </form>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('expenses.export', request()->all()) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide border-b border-slate-200">
                        <th class="text-left px-5 py-3 font-semibold">Tanggal</th>
                        <th class="text-left px-5 py-3 font-semibold">Order</th>
                        <th class="text-left px-5 py-3 font-semibold">Keterangan</th>
                        <th class="text-left px-5 py-3 font-semibold">Kategori</th>
                        <th class="text-left px-5 py-3 font-semibold">Bukti</th>
                        <th class="text-right px-5 py-3 font-semibold">Jumlah</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                    @php
                    $catColors = [
                        'bahan_baku'    => 'bg-blue-100 text-blue-700',
                        'operasional'   => 'bg-amber-100 text-amber-700',
                        'transportasi'  => 'bg-green-100 text-green-700',
                        'lainnya'       => 'bg-slate-100 text-slate-700',
                    ];
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap">{{ $exp->expense_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            @if($exp->order)
                            <a href="{{ route('orders.show', $exp->order) }}" class="font-mono text-xs font-bold text-brand-600 hover:underline">
                                {{ $exp->order->order_number }}
                            </a>
                            @else <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-700 font-medium">{{ $exp->description }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $catColors[$exp->category] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucwords(str_replace('_',' ',$exp->category)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($exp->attachment_path)
                            <a href="{{ Storage::url($exp->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Lihat
                            </a>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-semibold text-slate-800">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                @if($exp->order)
                                <a href="{{ route('orders.expenses.edit', [$exp->order, $exp]) }}"
                                   class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('orders.expenses.destroy', [$exp->order, $exp]) }}" method="POST"
                                      onsubmit="return confirm('Hapus pengeluaran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-slate-400">Belum ada data pengeluaran.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $expenses->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

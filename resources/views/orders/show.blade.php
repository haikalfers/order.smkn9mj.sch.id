@extends('layouts.app')
@section('title', $order->order_number)
@section('page-title', $order->order_number)
@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-brand-600">Pesanan</a> / {{ $order->order_number }}
@endsection

@section('header-actions')
<a href="{{ route('orders.print', $order) }}" target="_blank"
   class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
    Cetak
</a>
@endsection

@section('content')

@php
$statusFlow = ['pending','design_process','design_done','production','done','delivered'];
$currentIdx = array_search($order->status, $statusFlow);
@endphp

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Main --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Status Timeline --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">Alur Status</h3>
            <div class="flex items-center gap-0">
                @foreach($statusFlow as $i => $s)
                @php
                $labels = ['pending' => 'Pending', 'design_process' => 'Desain', 'design_done' => 'Selesai Desain', 'production' => 'Produksi', 'done' => 'Selesai', 'delivered' => 'Terkirim'];
                $isPast    = $i < $currentIdx;
                $isCurrent = $i === $currentIdx;
                $isFuture  = $i > $currentIdx;
                @endphp
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors
                        {{ $isPast    ? 'bg-brand-500 border-brand-500 text-white' : '' }}
                        {{ $isCurrent ? 'bg-brand-600 border-brand-600 text-white ring-4 ring-brand-100' : '' }}
                        {{ $isFuture  ? 'bg-white border-slate-300 text-slate-400' : '' }}
                        {{ $order->status === 'cancelled' && $i === 0 ? 'opacity-50' : '' }}
                    ">
                        @if($isPast)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                        {{ $i + 1 }}
                        @endif
                    </div>
                    <p class="text-xs mt-1.5 text-center leading-tight max-w-16
                        {{ $isCurrent ? 'text-brand-700 font-semibold' : 'text-slate-400' }}">
                        {{ $labels[$s] }}
                    </p>
                </div>
                @if($i < count($statusFlow) - 1)
                <div class="flex-1 h-0.5 mb-6 {{ $i < $currentIdx ? 'bg-brand-400' : 'bg-slate-200' }}"></div>
                @endif
                @endforeach

                @if($order->status === 'cancelled')
                <div class="ml-3 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-red-500 border-2 border-red-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                    <p class="text-xs mt-1.5 text-red-500 font-semibold">Dibatalkan</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Order Items --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Item Pesanan</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="text-left px-5 py-3 font-semibold">Produk</th>
                        <th class="text-left px-5 py-3 font-semibold">Spesifikasi</th>
                        <th class="text-left px-5 py-3 font-semibold">Desain Brief</th>
                        <th class="text-right px-5 py-3 font-semibold">Qty</th>
                        <th class="text-right px-5 py-3 font-semibold">Harga Satuan</th>
                        <th class="text-right px-5 py-3 font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-5 py-3.5 font-medium text-slate-800">{{ $item->product_name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">
                            @if($item->specifications)
                                @if(is_array($item->specifications))
                                    {{ implode(', ', $item->specifications) }}
                                @else
                                    {{ $item->specifications }}
                                @endif
                            @else —
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($item->design_file)
                            <a href="{{ Storage::url($item->design_file) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Lihat
                            </a>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right text-slate-700">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="px-5 py-3.5 text-right text-slate-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right font-semibold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200">
                        <td colspan="5" class="px-5 py-3 text-right font-bold text-slate-700">TOTAL</td>
                        <td class="px-5 py-3 text-right font-extrabold text-slate-900 text-base">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Status Log --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Riwayat Status</h3>
            </div>
            <div class="px-5 py-4 space-y-3">
                @forelse($order->statusLogs()->orderByDesc('created_at')->get() as $log)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-brand-400 mt-1.5 shrink-0"></div>
                    <div>
                        <div class="flex items-center gap-2">
                            <x-status-badge :status="$log->status"/>
                            <span class="text-xs text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($log->notes)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $log->notes }}</p>
                        @endif
                        @if($log->user)
                        <p class="text-xs text-slate-400">oleh {{ $log->user->name }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400">Belum ada riwayat.</p>
                @endforelse
            </div>
        </div>

        {{-- Expenses --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Pengeluaran</h3>
                @if(in_array(auth()->user()->role, ['super_admin','admin']))
                <a href="{{ route('orders.expenses.create', $order) }}" class="text-xs bg-brand-50 text-brand-700 font-semibold px-3 py-1.5 rounded-lg hover:bg-brand-100 transition-colors">
                    + Tambah
                </a>
                @endif
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="text-left px-5 py-2 font-semibold">Keterangan</th>
                        <th class="text-left px-5 py-2 font-semibold">Kategori</th>
                        <th class="text-left px-5 py-2 font-semibold">Tanggal</th>
                        <th class="text-left px-5 py-2 font-semibold">Bukti</th>
                        <th class="text-right px-5 py-2 font-semibold">Jumlah</th>
                        @if(in_array(auth()->user()->role, ['super_admin','admin']))
                        <th class="px-5 py-2"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($order->expenses as $exp)
                    <tr>
                        <td class="px-5 py-3 text-slate-700">{{ $exp->description }}</td>
                        <td class="px-5 py-3 text-slate-500 capitalize">{{ str_replace('_', ' ', $exp->category) }}</td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $exp->expense_date->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            @if($exp->attachment_path)
                            <a href="{{ Storage::url($exp->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Lihat
                            </a>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        @if(in_array(auth()->user()->role, ['super_admin','admin']))
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('orders.expenses.edit', [$order, $exp]) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('orders.expenses.destroy', [$order, $exp]) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-6 text-center text-slate-400 text-sm">Belum ada pengeluaran dicatat.</td></tr>
                    @endforelse
                </tbody>
                @if($order->expenses->count() > 0)
                <tfoot>
                    <tr class="bg-slate-50 border-t border-slate-200">
                        <td colspan="{{ in_array(auth()->user()->role, ['super_admin','admin']) ? 4 : 4 }}" class="px-5 py-2.5 text-right font-bold text-slate-700 text-sm">Total Pengeluaran</td>
                        <td class="px-5 py-2.5 text-right font-extrabold text-slate-900">Rp {{ number_format($order->expenses->sum('amount'), 0, ',', '.') }}</td>
                        @if(in_array(auth()->user()->role, ['super_admin','admin']))<td></td>@endif
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Pembayaran</h3>
            </div>

            {{-- Payment Record Form --}}
            @if(in_array(auth()->user()->role, ['super_admin','admin']))
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                <form action="{{ route('orders.payments.store', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Tanggal</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Jumlah (Rp)</label>
                            <input type="number" name="amount" min="0.01" step="0.01" placeholder="0" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Tipe Pembayaran</label>
                            <select name="payment_type" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                                <option value="">-- Pilih --</option>
                                <option value="dp">DP (Down Payment)</option>
                                <option value="cicilan">Cicilan</option>
                                <option value="pelunasan">Pelunasan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Metode Pembayaran</label>
                            <select name="method" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                                <option value="">-- Pilih --</option>
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="cek">Cek</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Referensi (Opsional)</label>
                            <input type="text" name="reference" placeholder="No. Kwitansi / Transfer ID" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Catatan (Opsional)</label>
                            <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Catat Pembayaran
                    </button>
                </form>
            </div>
            @endif

            {{-- Payment History Table --}}
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="text-left px-5 py-2 font-semibold">Tanggal</th>
                        <th class="text-left px-5 py-2 font-semibold">Tipe</th>
                        <th class="text-left px-5 py-2 font-semibold">Metode</th>
                        <th class="text-right px-5 py-2 font-semibold">Jumlah</th>
                        <th class="text-left px-5 py-2 font-semibold">Pencatat</th>
                        @if(in_array(auth()->user()->role, ['super_admin','admin']))
                        <th class="px-5 py-2"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($order->payments as $payment)
                    <tr>
                        <td class="px-5 py-3 text-slate-700 text-xs">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                {{ $payment->payment_type === 'dp' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $payment->payment_type === 'cicilan' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $payment->payment_type === 'pelunasan' ? 'bg-green-100 text-green-800' : '' }}
                            ">
                                {{ $payment->payment_type === 'dp' ? 'DP' : ($payment->payment_type === 'cicilan' ? 'Cicilan' : 'Pelunasan') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 capitalize">{{ $payment->method }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $payment->recorder->name ?? 'N/A' }}</td>
                        @if(in_array(auth()->user()->role, ['super_admin','admin']))
                        <td class="px-5 py-3">
                            <form action="{{ route('orders.payments.destroy', [$order, $payment]) }}" method="POST" onsubmit="return confirm('Hapus pembayaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ in_array(auth()->user()->role, ['super_admin','admin']) ? 6 : 5 }}" class="px-5 py-6 text-center text-slate-400 text-sm">Belum ada pembayaran dicatat.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Payment Summary --}}
            <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 grid sm:grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Harga</dt>
                    <dd class="mt-1 text-lg font-extrabold text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Dibayar</dt>
                    <dd class="mt-1 text-lg font-extrabold text-green-600">Rp {{ number_format($order->total_paid, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sisa Pembayaran</dt>
                    <dd class="mt-1 text-lg font-extrabold text-slate-900">Rp {{ number_format($order->remaining_balance, 0, ',', '.') }}</dd>
                </div>
            </div>

            {{-- Payment Status Badge --}}
            <div class="px-5 py-4 border-t border-slate-100 bg-white">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Status Pembayaran</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->payment_status === 'dp' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $order->payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                    ">
                        {{ $order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'dp' ? 'DP' : 'Belum Bayar') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

        {{-- Info Card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">Informasi Order</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pelanggan</dt>
                    <dd class="mt-0.5 font-semibold text-slate-800">{{ $order->customer->name }}</dd>
                    <dd class="text-slate-500 text-xs">{{ $order->customer->phone }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</dt>
                    <dd class="mt-1"><x-status-badge :status="$order->status"/></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pembayaran</dt>
                    <dd class="mt-0.5 text-slate-700 capitalize">{{ str_replace('_', ' ', $order->payment_status) }}
                        @if($order->dp_amount > 0)
                        <span class="text-xs text-slate-500">(DP: Rp {{ number_format($order->dp_amount, 0, ',', '.') }})</span>
                        @endif
                    </dd>
                </div>
                @if($order->deadline)
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Deadline</dt>
                    <dd class="mt-0.5 {{ $order->deadline->isPast() && !in_array($order->status, ['done','delivered','cancelled']) ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                        {{ $order->deadline->format('d M Y') }}
                        @if($order->deadline->isPast() && !in_array($order->status, ['done','delivered','cancelled']))
                        <span class="text-xs">(Overdue!)</span>
                        @endif
                    </dd>
                </div>
                @endif
                @if($order->notes)
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Catatan</dt>
                    <dd class="mt-0.5 text-slate-600 text-xs">{{ $order->notes }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Dibuat</dt>
                    <dd class="mt-0.5 text-slate-600 text-xs">{{ $order->created_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Action Card --}}
        @if(in_array(auth()->user()->role, ['super_admin','admin']) && $order->status !== 'cancelled')
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-3">Aksi</h3>
            @php
            $nextStatus = [
                'pending'        => ['label' => 'Mulai Proses Desain', 'value' => 'design_process', 'color' => 'bg-blue-600 hover:bg-blue-700'],
                'design_process' => null,
                'design_done'    => ['label' => 'Mulai Produksi', 'value' => 'production', 'color' => 'bg-purple-600 hover:bg-purple-700'],
                'production'     => null,
                'done'           => ['label' => 'Tandai Terkirim', 'value' => 'delivered', 'color' => 'bg-teal-600 hover:bg-teal-700'],
                'delivered'      => null,
            ][$order->status] ?? null;
            @endphp

            @if($nextStatus)
            <form action="{{ route('orders.update-status', $order) }}" method="POST" class="mb-3">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $nextStatus['value'] }}">
                
                {{-- Assign Desainer saat Status Desain --}}
                @if($nextStatus['value'] === 'design_process')
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tugaskan ke Desainer <span class="text-amber-600">*</span></label>
                    <select name="design_assigned_to" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                        <option value="">-- Pilih Desainer --</option>
                        @forelse(\App\Models\User::where('role', 'desain')->orderBy('name')->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @empty
                        <option value="" disabled>Tidak ada desainer tersedia</option>
                        @endforelse
                    </select>
                </div>
                @endif

                {{-- Assign Cetak/Produksi saat Status Produksi --}}
                @if($nextStatus['value'] === 'production')
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tugaskan ke Bagian Cetak/Produksi <span class="text-amber-600">*</span></label>
                    <select name="production_assigned_to" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                        <option value="">-- Pilih Cetak/Produksi --</option>
                        @forelse(\App\Models\User::where('role', 'cetak')->orderBy('name')->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @empty
                        <option value="" disabled>Tidak ada bagian cetak tersedia</option>
                        @endforelse
                    </select>
                </div>
                @endif
                
                <div class="mb-2">
                    <textarea name="notes" rows="2" placeholder="Catatan (opsional)…"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:border-brand-500 outline-none resize-none"></textarea>
                </div>
                <button type="submit" class="w-full {{ $nextStatus['color'] }} text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    {{ $nextStatus['label'] }}
                </button>
            </form>
            @endif

            @if(!in_array($order->status, ['done','delivered']))
            <form action="{{ route('orders.update-status', $order) }}" method="POST"
                  onsubmit="return confirm('Batalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    Batalkan Order
                </button>
            </form>
            @endif

            @if(in_array($order->status, ['pending','design_process']))
            <a href="{{ route('orders.edit', $order) }}"
               class="mt-2 block w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-lg transition-colors text-sm">
                Edit Pesanan
            </a>
            @endif
        </div>
        @endif

        {{-- Design Task --}}
        @if($order->designTask)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800">Task Desain</h3>
                <a href="{{ route('design-tasks.show', $order->designTask) }}" class="text-xs text-brand-600 hover:underline">Detail →</a>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Ditugaskan ke</dt>
                    <dd class="font-medium text-slate-700">{{ $order->designTask->assignedUser?->name ?? 'Belum ditugaskan' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Progress</dt>
                    <dd class="font-medium text-slate-700">{{ $order->designTask->progress ?? 0 }}%</dd>
                </div>
            </dl>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5">
                <div class="bg-blue-500 h-1.5 rounded-full" :style="`width: {{ $order->designTask->progress ?? 0 }}%`"></div>
            </div>
        </div>
        @endif

        {{-- Production Task --}}
        @if($order->productionTask)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800">Task Produksi</h3>
                <a href="{{ route('production-tasks.show', $order->productionTask) }}" class="text-xs text-brand-600 hover:underline">Detail →</a>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Ditugaskan ke</dt>
                    <dd class="font-medium text-slate-700">{{ $order->productionTask->assignedUser?->name ?? 'Belum ditugaskan' }}</dd>
                </div>
                @if($order->productionTask->started_at)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Mulai</dt>
                    <dd class="font-medium text-slate-700">{{ $order->productionTask->started_at->format('d M Y') }}</dd>
                </div>
                @endif
            </dl>
        </div>
        @endif
    </div>
</div>
@endsection

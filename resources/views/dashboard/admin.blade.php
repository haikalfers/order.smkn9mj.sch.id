@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            label="Total Pesanan Aktif"
            :value="$stats['active_orders'] ?? 0"
            color="brand"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        />
        <x-stat-card
            label="Selesai Bulan Ini"
            :value="$stats['done_this_month'] ?? 0"
            color="green"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            label="Pengeluaran Bulan Ini"
            :value="'Rp ' . number_format($stats['expenses_this_month'] ?? 0, 0, ',', '.')"
            color="amber"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            label="Order Overdue"
            :value="$stats['overdue_orders'] ?? 0"
            color="red"
            :sub="'Melewati deadline'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
        />
    </div>

    {{-- Status Breakdown --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
        $statusItems = [
            ['key' => 'pending',        'label' => 'Pending',        'class' => 'border-amber-200 bg-amber-50',   'dot' => 'bg-amber-400'],
            ['key' => 'design_process', 'label' => 'Proses Desain',  'class' => 'border-blue-200 bg-blue-50',     'dot' => 'bg-blue-500'],
            ['key' => 'design_done',    'label' => 'Desain Selesai', 'class' => 'border-indigo-200 bg-indigo-50', 'dot' => 'bg-indigo-500'],
            ['key' => 'production',     'label' => 'Produksi',       'class' => 'border-purple-200 bg-purple-50', 'dot' => 'bg-purple-500'],
            ['key' => 'done',           'label' => 'Selesai',        'class' => 'border-green-200 bg-green-50',   'dot' => 'bg-green-500'],
            ['key' => 'delivered',      'label' => 'Terkirim',       'class' => 'border-teal-200 bg-teal-50',     'dot' => 'bg-teal-500'],
        ];
        @endphp
        @foreach($statusItems as $item)
        <div class="rounded-xl border {{ $item['class'] }} px-4 py-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-2 h-2 rounded-full {{ $item['dot'] }}"></div>
                <span class="text-xs text-slate-500">{{ $item['label'] }}</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800">{{ $statusCounts[$item['key']] ?? 0 }}</p>
        </div>
        @endforeach
    </div>

    {{-- Chart + Recent Orders --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Orders Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Pesanan 12 Bulan Terakhir</h3>
            </div>
            <canvas id="ordersChart" height="100"></canvas>
        </div>

        {{-- Overdue Orders --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">
                Order Overdue
                @if(($overdueOrders ?? collect())->count() > 0)
                <span class="ml-2 bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $overdueOrders->count() }}</span>
                @endif
            </h3>
            @forelse($overdueOrders ?? [] as $order)
            <div class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-slate-800 hover:text-brand-600 truncate block">
                        {{ $order->order_number }}
                    </a>
                    <p class="text-xs text-slate-500 truncate">{{ $order->customer->name }}</p>
                    <p class="text-xs text-red-500 mt-0.5">Deadline: {{ $order->deadline->format('d M Y') }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <svg class="w-10 h-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-slate-400">Tidak ada order overdue</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Pesanan Terbaru</h3>
            <a href="{{ route('orders.index') }}" class="text-brand-600 text-sm font-medium hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="text-left px-5 py-3 font-semibold">No. Order</th>
                        <th class="text-left px-5 py-3 font-semibold">Pelanggan</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-left px-5 py-3 font-semibold">Deadline</th>
                        <th class="text-left px-5 py-3 font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders ?? [] as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('orders.show', $order) }}" class="font-semibold text-brand-600 hover:underline font-mono text-xs">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-slate-700">{{ $order->customer->name }}</td>
                        <td class="px-5 py-3"><x-status-badge :status="$order->status"/></td>
                        <td class="px-5 py-3 text-slate-500">
                            @if($order->deadline)
                                <span class="{{ $order->deadline->isPast() && !in_array($order->status, ['done','delivered','cancelled']) ? 'text-red-500 font-semibold' : '' }}">
                                    {{ $order->deadline->format('d M Y') }}
                                </span>
                            @else — @endif
                        </td>
                        <td class="px-5 py-3 font-semibold text-slate-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const labels = @json($chartLabels ?? []);
const data   = @json($chartData ?? []);
const ctx = document.getElementById('ordersChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah Pesanan',
                data,
                backgroundColor: 'rgba(14, 165, 233, 0.2)',
                borderColor: 'rgb(14, 165, 233)',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush

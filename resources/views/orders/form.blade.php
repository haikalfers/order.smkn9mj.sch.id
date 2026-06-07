@extends('layouts.app')
@section('title', isset($order) ? 'Edit Pesanan' : 'Buat Pesanan Baru')
@section('page-title', isset($order) ? 'Edit Pesanan' : 'Buat Pesanan Baru')
@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-brand-600">Pesanan</a> / {{ isset($order) ? $order->order_number : 'Baru' }}
@endsection

@section('content')
<form action="{{ isset($order) ? route('orders.update', $order) : route('orders.store') }}" method="POST" enctype="multipart/form-data" x-data="orderForm()" @submit="submitForm($event)">
    @csrf
    @if(isset($order)) @method('PUT') @endif

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Left: Main Info --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Customer & Basic Info --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-800 mb-4">Informasi Pesanan</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pelanggan <span class="text-red-500">*</span></label>
                        <select name="customer_id" required
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm bg-white">
                            <option value="">— Pilih Pelanggan —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id', $order->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }})
                            </option>
                            @endforeach
                        </select>
                        <a href="{{ route('customers.create') }}" class="text-xs text-brand-600 hover:underline mt-1 inline-block">+ Tambah pelanggan baru</a>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deadline</label>
                        <input type="date" name="deadline" value="{{ old('deadline', isset($order) ? $order->deadline?->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Pembayaran</label>
                        <select name="payment_status"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm bg-white">
                            @foreach(['unpaid' => 'Belum Bayar', 'dp' => 'DP / Uang Muka', 'paid' => 'Lunas'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('payment_status', $order->payment_status ?? 'unpaid') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah DP (Rp)</label>
                        <input type="number" name="dp_amount" min="0"
                               value="{{ old('dp_amount', $order->dp_amount ?? 0) }}"
                               class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                               placeholder="0">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm resize-none"
                                  placeholder="Catatan tambahan untuk order ini…">{{ old('notes', $order->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800">Item Pesanan</h3>
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1.5 text-sm bg-brand-50 text-brand-700 font-semibold px-3 py-1.5 rounded-lg hover:bg-brand-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="border border-slate-200 rounded-xl p-4 relative">
                            <button type="button" @click="removeItem(index)"
                                    class="absolute top-3 right-3 w-6 h-6 rounded-full bg-red-100 text-red-500 hover:bg-red-200 flex items-center justify-center transition-colors"
                                    x-show="items.length > 1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Item / Produk <span class="text-red-500">*</span></label>
                                    <input type="text" :name="`items[${index}][product_name]`" x-model="item.product_name" required
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                                           placeholder="Contoh: Kaos Polo, Banner 3x2m…">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                    <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required
                                           @input="calcTotal()"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan</label>
                                    <input type="text" :name="`items[${index}][unit]`" x-model="item.unit"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm"
                                           placeholder="pcs, lembar, set…">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Satuan (Rp)</label>
                                    <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" min="0"
                                           @input="calcTotal()"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Subtotal</label>
                                    <div class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-700">
                                        Rp <span x-text="(item.quantity * item.unit_price).toLocaleString('id-ID')"></span>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Spesifikasi (ukuran, warna, material, dll)</label>
                                    <input type="text" :name="`items[${index}][specifications]`" x-model="item.specifications"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm"
                                           placeholder="Contoh: 40x60cm, warna merah, bahan kain cotton…">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Upload Desain / Foto</label>
                                    <input type="file" :name="`items[${index}][design_file]`" accept="image/*,.pdf,.ai,.psd"
                                           @change="item.design_file_name = $event.target.files[0]?.name"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, PDF, AI, PSD (Maks. 10MB)</p>
                                    <template x-if="item.design_file_name">
                                        <p class="text-xs text-green-600 mt-1.5" x-text="`✓ ${item.design_file_name}`"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Total --}}
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-end gap-4">
                    <span class="text-slate-500 text-sm font-medium">Total Harga:</span>
                    <span class="text-xl font-extrabold text-slate-900">Rp <span x-text="totalFormatted"></span></span>
                    <input type="hidden" name="total_price" :value="total">
                </div>
            </div>
        </div>

        {{-- Right: Submit --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-slate-200 p-5 sticky top-6">
                <h3 class="font-bold text-slate-800 mb-4">Simpan Pesanan</h3>

                <div class="space-y-3">
                    <button type="submit"
                            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-lg transition-colors text-sm">
                        {{ isset($order) ? 'Update Pesanan' : 'Buat Pesanan' }}
                    </button>
                    <a href="{{ route('orders.index') }}"
                       class="block w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 rounded-lg transition-colors text-sm">
                        Batal
                    </a>
                </div>

                @if(isset($order))
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">No. Order</p>
                    <p class="font-mono font-bold text-slate-800">{{ $order->order_number }}</p>
                    <p class="text-xs text-slate-500 mt-2 mb-1">Dibuat</p>
                    <p class="text-xs text-slate-700">{{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@php
$existingItemsData = old('items', isset($order) ? $order->items->map(function($i) {
    return [
        'product_name' => $i->product_name,
        'quantity' => $i->quantity,
        'unit' => $i->unit ?? '',
        'unit_price' => $i->unit_price,
        'specifications' => is_array($i->specifications) ? implode(', ', $i->specifications) : ($i->specifications ?? ''),
        'design_file_name' => null
    ];
})->values()->toArray() : []);
@endphp
<script>
function orderForm() {
    const existingItems = JSON.parse('{{ json_encode($existingItemsData) }}');

    return {
        items: existingItems.length ? existingItems : [{ product_name: '', quantity: 1, unit: 'pcs', unit_price: 0, specifications: '', design_file_name: null }],
        total: 0,
        totalFormatted: '0',
        init() { this.calcTotal(); },
        addItem() {
            this.items.push({ product_name: '', quantity: 1, unit: 'pcs', unit_price: 0, specifications: '', design_file_name: null });
        },
        removeItem(i) {
            this.items.splice(i, 1);
            this.calcTotal();
        },
        calcTotal() {
            this.total = this.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0);
            this.totalFormatted = this.total.toLocaleString('id-ID');
        },
        submitForm(e) {
            // Form submission berjalan normal, file inputs akan disubmit secara otomatis
            // Tidak perlu preventDefault karena FormData handle native form submission
        }
    }
}
</script>
@endpush

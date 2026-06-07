@extends('layouts.app')
@section('title', isset($expense) ? 'Edit Pengeluaran' : 'Tambah Pengeluaran')
@section('page-title', isset($expense) ? 'Edit Pengeluaran' : 'Tambah Pengeluaran')
@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-brand-600">Pesanan</a> /
    <a href="{{ route('orders.show', $order) }}" class="hover:text-brand-600">{{ $order->order_number }}</a> /
    {{ isset($expense) ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}
@endsection

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-slate-200 p-1 mb-4">
        <div class="bg-amber-50 rounded-lg px-4 py-3 flex items-center gap-3">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            <p class="text-sm text-amber-700">Pengeluaran untuk order <strong>{{ $order->order_number }}</strong> — {{ $order->customer->name }}</p>
        </div>
    </div>

    <form action="{{ isset($expense) ? route('orders.expenses.update', [$order, $expense]) : route('orders.expenses.store', $order) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($expense)) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description', $expense->description ?? '') }}" required
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                       placeholder="Contoh: Beli tinta, Ongkos kirim…">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm bg-white">
                        <option value="">— Pilih —</option>
                        @foreach(['bahan_baku' => 'Bahan Baku', 'operasional' => 'Operasional', 'transportasi' => 'Transportasi', 'lainnya' => 'Lainnya'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('category', $expense->category ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" required
                           value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" min="0" required
                       value="{{ old('amount', $expense->amount ?? '') }}"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                       placeholder="0">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Tambahan</label>
                <input type="text" name="notes" value="{{ old('notes', $expense->notes ?? '') }}"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm"
                       placeholder="Opsional">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Upload Nota/Bukti Pembelian</label>
                <div class="relative">
                    <input type="file" name="attachment_path" accept="image/*,.pdf"
                           class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                           placeholder="Pilih file…">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, PDF (Maks. 5MB)</p>
                </div>
                @if(isset($expense) && $expense->attachment_path)
                <div class="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-700 font-medium">File saat ini:</p>
                    <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank" class="text-blue-600 hover:underline text-xs mt-1 inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        {{ basename($expense->attachment_path) }}
                    </a>
                </div>
                @endif
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                    {{ isset($expense) ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('orders.show', $order) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

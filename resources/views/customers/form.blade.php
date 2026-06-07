@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('page-title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('breadcrumb')
    <a href="{{ route('customers.index') }}" class="hover:text-brand-600">Pelanggan</a> / {{ isset($customer) ? $customer->name : 'Baru' }}
@endsection

@section('content')
<div class="max-w-2xl">
    <form action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}" method="POST">
        @csrf
        @if(isset($customer)) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}"
                           class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                           placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
                           class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat</label>
                <textarea name="address" rows="3"
                          class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm resize-none">{{ old('address', $customer->address ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes', $customer->notes ?? '') }}"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                       placeholder="Opsional">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                    {{ isset($customer) ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('customers.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

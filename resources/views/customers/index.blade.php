@extends('layouts.app')
@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('header-actions')
<a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Tambah Pelanggan
</a>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Search --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form action="{{ route('customers.index') }}" method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama / telepon / email…"
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">Cari</button>
            @if(request('search'))
            <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide border-b border-slate-200">
                        <th class="text-left px-5 py-3 font-semibold">Nama</th>
                        <th class="text-left px-5 py-3 font-semibold">Telepon / WA</th>
                        <th class="text-left px-5 py-3 font-semibold">Email</th>
                        <th class="text-left px-5 py-3 font-semibold">Alamat</th>
                        <th class="text-center px-5 py-3 font-semibold">Total Order</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </div>
                                <span class="font-semibold text-slate-800">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $customer->email ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs max-w-xs truncate">{{ $customer->address ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $customer->orders_count ?? $customer->orders->count() }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('customers.edit', $customer) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                      onsubmit="return confirm('Hapus pelanggan {{ $customer->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400">Belum ada pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $customers->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

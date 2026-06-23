@extends('layouts.app')
@section('title', 'Detail Tugas Desain')
@section('page-title', 'Tugas Desain')
@section('breadcrumb')
    <a href="{{ route('design-tasks.index') }}" class="hover:text-brand-600">Tugas Desain</a> / {{ $task->order->order_number }}
@endsection

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Task Info --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Informasi Task</h3>
                @php
                $sc = [
                    'waiting' => 'bg-slate-100 text-slate-700',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'revision' => 'bg-amber-100 text-amber-800',
                    'done' => 'bg-green-100 text-green-800'
                ];
                $sl = [
                    'waiting' => 'Menunggu',
                    'in_progress' => 'Sedang Dikerjakan',
                    'revision' => 'Revisi',
                    'done' => 'Selesai'
                ];
                @endphp
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
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ditugaskan ke</dt>
                    <dd class="font-medium text-slate-800">{{ $task->assignedUser?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Deadline Order</dt>
                    <dd class="{{ $task->order->deadline?->isPast() ? 'text-red-500 font-semibold' : 'text-slate-800 font-medium' }}">
                        {{ $task->order->deadline?->format('d M Y') ?? '—' }}
                    </dd>
                </div>
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
                <h3 class="font-bold text-slate-800">Item Pesanan</h3>
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
                        <td class="px-5 py-3 text-right text-slate-700">{{ $item->quantity }} {{ $item->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Files --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">File Hasil Desain</h3>
            @if($task->files && count($task->files) > 0)
            <div class="space-y-2 mb-4">
                @foreach($task->files as $file)
                <div class="flex items-center gap-3 bg-slate-50 rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <a href="{{ Storage::url($file) }}" target="_blank" class="flex-1 text-sm text-brand-700 hover:underline truncate">{{ basename($file) }}</a>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-400 text-sm mb-4">Belum ada file diupload.</p>
            @endif

            @if(in_array(auth()->user()->role, ['super_admin','admin','desain']) && $task->status !== 'done')
            <form action="{{ route('design-tasks.upload', $task) }}" method="POST" enctype="multipart/form-data"
                  class="border-2 border-dashed border-slate-300 rounded-xl p-5 text-center hover:border-brand-400 transition-colors">
                @csrf
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-slate-500 mb-3">Upload file desain (AI, PSD, PDF, ZIP, PNG, JPG · max 500MB)</p>
                <input type="file" name="file" required accept=".ai,.psd,.pdf,.zip,.png,.jpg,.jpeg"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                <button type="submit" class="mt-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors">Upload</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Sidebar Actions --}}
    <div class="space-y-5">
        @if(in_array(auth()->user()->role, ['super_admin','admin','desain']) && $task->status !== 'done')
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">Update Progress</h3>
            <form action="{{ route('design-tasks.update', $task) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Progress: <span id="prog-label">{{ $task->progress ?? 0 }}%</span></label>
                    <input type="range" name="progress" min="0" max="100" value="{{ $task->progress ?? 0 }}"
                           oninput="document.getElementById('prog-label').textContent = this.value + '%'"
                           class="w-full accent-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                        @foreach(['waiting' => 'Menunggu', 'in_progress' => 'Sedang Dikerjakan', 'revision' => 'Revisi', 'done' => 'Selesai'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $task->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @if($task->status !== 'done')
                    <p class="text-xs text-amber-600 mt-1">⚠ Tandai "Selesai" untuk memindahkan order ke status Desain Selesai.</p>
                    @endif
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none resize-none"
                              placeholder="Opsional…">{{ $task->notes }}</textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    Simpan Update
                </button>
            </form>
        </div>

        @if(!$task->assigned_to && auth()->user()->role === 'desain')
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <form action="{{ route('design-tasks.assign', $task) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    Ambil Task Ini
                </button>
            </form>
        </div>
        @endif

        {{-- Admin Assign Designer --}}
        @if(!$task->assigned_to && in_array(auth()->user()->role, ['super_admin', 'admin']))
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-3">Tugaskan Desainer</h3>
            <form action="{{ route('design-tasks.admin-assign', $task) }}" method="POST">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Desainer</label>
                    <select name="assigned_to" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                        <option value="">-- Pilih Desainer --</option>
                        @forelse(\App\Models\User::where('role', 'desain')->orderBy('name')->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @empty
                        <option value="" disabled>Tidak ada desainer tersedia</option>
                        @endforelse
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    Tugaskan
                </button>
            </form>
        </div>
        @endif
        @endif

        <a href="{{ route('orders.show', $task->order) }}"
           class="flex items-center gap-2 bg-white rounded-xl border border-slate-200 px-5 py-4 text-sm font-semibold text-brand-600 hover:bg-brand-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Lihat Detail Order
        </a>
    </div>
</div>
@endsection

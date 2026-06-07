@extends('layouts.app')
@section('title', 'Tugas Produksi')
@section('page-title', 'Tugas Produksi')

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp
<div class="space-y-4">
    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form action="{{ route('production-tasks.index') }}" method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                <option value="">Semua Status</option>
                @foreach(['waiting' => 'Menunggu', 'in_progress' => 'Sedang Produksi', 'done' => 'Selesai'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">Filter</button>
            @if(request('status'))
            <a href="{{ route('production-tasks.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">Reset</a>
            @endif
        </form>
    </div>

    <div class="grid gap-4">
        @forelse($tasks as $task)
        @php
        $sc = [
            'waiting' => 'bg-slate-100 text-slate-700',
            'in_progress' => 'bg-purple-100 text-purple-800',
            'done' => 'bg-green-100 text-green-800'
        ];
        $sl = [
            'waiting' => 'Menunggu',
            'in_progress' => 'Sedang Produksi',
            'done' => 'Selesai'
        ];
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('production-tasks.show', $task) }}" class="font-bold text-slate-800 hover:text-brand-600">
                                {{ $task->order->order_number }}
                            </a>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$task->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $sl[$task->status] ?? $task->status }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-sm mt-0.5">{{ $task->order->customer->name }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                            <span>Operator: <strong class="text-slate-700">{{ $task->assignedUser?->name ?? 'Belum ditugaskan' }}</strong></span>
                            @if($task->started_at)
                            <span>Mulai: <strong class="text-slate-700">{{ $task->started_at->format('d M Y') }}</strong></span>
                            @endif
                            @if($task->order->deadline)
                            <span>Deadline: <strong class="{{ $task->order->deadline->isPast() ? 'text-red-500' : 'text-slate-700' }}">{{ $task->order->deadline->format('d M Y') }}</strong></span>
                            @endif
                        </div>
                        @if($task->notes)
                        <p class="text-xs text-slate-400 mt-1">{{ $task->notes }}</p>
                        @endif
                    </div>
                </div>
                <a href="{{ route('production-tasks.show', $task) }}" class="inline-flex items-center gap-1.5 text-xs bg-slate-100 text-slate-700 font-semibold px-3 py-1.5 rounded-lg hover:bg-slate-200 transition-colors">
                    Detail / Update
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18"/>
            </svg>
            <p class="text-slate-400">Tidak ada tugas produksi.</p>
        </div>
        @endforelse
    </div>

    @if($tasks->hasPages())
    <div>{{ $tasks->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

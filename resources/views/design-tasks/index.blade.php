@extends('layouts.app')
@section('title', 'Tugas Desain')
@section('page-title', 'Tugas Desain')

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp
<div class="space-y-4">
    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form action="{{ route('design-tasks.index') }}" method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                <option value="">Semua Status</option>
                @foreach(['waiting' => 'Menunggu', 'in_progress' => 'Sedang Dikerjakan', 'revision' => 'Revisi', 'done' => 'Selesai'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            
            <select name="assigned_to" class="px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 outline-none bg-white">
                <option value="">Semua Penugasan</option>
                <option value="null" {{ request('assigned_to') === 'null' ? 'selected' : '' }}>Belum Ditugaskan</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">Filter</button>
            @if(request('status') || request('assigned_to'))
            <a href="{{ route('design-tasks.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">Reset</a>
            @endif
        </form>
    </div>

    <div class="grid gap-4">
        @forelse($tasks as $task)
        @php
        $statusColors = [
            'waiting' => 'bg-slate-100 text-slate-700',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'revision' => 'bg-amber-100 text-amber-800',
            'done' => 'bg-green-100 text-green-800'
        ];
        $statusLabels = [
            'waiting' => 'Menunggu',
            'in_progress' => 'Sedang Dikerjakan',
            'revision' => 'Revisi',
            'done' => 'Selesai'
        ];
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.83a4 4 0 01-1.897 1.06l-2.977.744.744-2.977a4 4 0 011.06-1.897z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('design-tasks.show', $task) }}" class="font-bold text-slate-800 hover:text-brand-600">
                                {{ $task->order->order_number }}
                            </a>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$task->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $statusLabels[$task->status] ?? $task->status }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-sm mt-0.5">{{ $task->order->customer->name }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                            <span>Ditugaskan: <strong class="text-slate-700">{{ $task->assignedUser?->name ?? 'Belum ditugaskan' }}</strong></span>
                            @if($task->order->deadline)
                            <span>Deadline: <strong class="{{ $task->order->deadline->isPast() ? 'text-red-500' : 'text-slate-700' }}">{{ $task->order->deadline->format('d M Y') }}</strong></span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('design-tasks.show', $task) }}" class="inline-flex items-center gap-1.5 text-xs bg-slate-100 text-slate-700 font-semibold px-3 py-1.5 rounded-lg hover:bg-slate-200 transition-colors">
                        Detail
                    </a>
                    @if(auth()->user()->role === 'desain' && !$task->assigned_to)
                    <form action="{{ route('design-tasks.assign', $task) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs bg-amber-100 text-amber-700 font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-200 transition-colors">
                            Ambil Task
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                    <span>Progress Desain</span>
                    <span class="font-semibold text-slate-700">{{ $task->progress ?? 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all" style="width:{{ $task->progress ?? 0 }}%"></div>
                </div>
            </div>

            {{-- Files --}}
            @if($task->files && count($task->files) > 0)
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($task->files as $file)
                <a href="{{ Storage::url($file) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-xs bg-brand-50 text-brand-700 font-medium px-3 py-1.5 rounded-lg hover:bg-brand-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    {{ basename($file) }}
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343"/></svg>
            <p class="text-slate-400">Tidak ada tugas desain.</p>
        </div>
        @endforelse
    </div>

    @if($tasks->hasPages())
    <div>{{ $tasks->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

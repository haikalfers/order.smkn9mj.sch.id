@extends('layouts.app')
@section('title', 'Dashboard Desain')
@section('page-title', 'Dashboard Desain')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-2 gap-4">
        <x-stat-card
            label="Task Aktif Saya"
            :value="$myActiveTasks ?? 0"
            color="brand"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343"/></svg>'
        />
        <x-stat-card
            label="Menunggu Ditugaskan"
            :value="($unassignedTasks ?? collect())->count()"
            color="amber"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- My Active Tasks --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Task Aktif Saya</h3>
                <a href="{{ route('design-tasks.index') }}" class="text-brand-600 text-sm font-medium hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($activeTasks ?? [] as $task)
                <div class="px-5 py-4 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.83a4 4 0 01-1.897 1.06l-2.977.744.744-2.977a4 4 0 011.06-1.897z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('design-tasks.show', $task) }}" class="text-sm font-semibold text-slate-800 hover:text-brand-600">
                            {{ $task->order->order_number }}
                        </a>
                        <p class="text-xs text-slate-500">{{ $task->order->customer->name }}</p>
                        <div class="mt-1.5">
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $task->progress ?? 0 }}%"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $task->progress ?? 0 }}% selesai</p>
                        </div>
                    </div>
                    <a href="{{ route('design-tasks.show', $task) }}" class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg font-semibold hover:bg-blue-100 transition-colors shrink-0">
                        Update
                    </a>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">Tidak ada task aktif.</div>
                @endforelse
            </div>
        </div>

        {{-- Unassigned Tasks --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Task Belum Ditugaskan</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($unassignedTasks ?? [] as $task)
                <div class="px-5 py-4 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $task->order->order_number }}</p>
                        <p class="text-xs text-slate-500">{{ $task->order->customer->name }}</p>
                        <p class="text-xs text-amber-600 mt-0.5">Masuk: {{ $task->created_at->diffForHumans() }}</p>
                    </div>
                    <form action="{{ route('design-tasks.assign', $task) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg font-semibold hover:bg-amber-100 transition-colors shrink-0">
                            Ambil Task
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">Semua task sudah ditugaskan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

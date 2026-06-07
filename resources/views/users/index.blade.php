@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('header-actions')
<a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Tambah User
</a>
@endsection

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide border-b border-slate-200">
                        <th class="text-left px-5 py-3 font-semibold">Nama</th>
                        <th class="text-left px-5 py-3 font-semibold">Email</th>
                        <th class="text-left px-5 py-3 font-semibold">Role</th>
                        <th class="text-center px-5 py-3 font-semibold">Status</th>
                        <th class="text-left px-5 py-3 font-semibold">Bergabung</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    @php
                    $roleColors = [
                        'super_admin' => 'bg-red-100 text-red-700',
                        'admin'       => 'bg-brand-100 text-brand-700',
                        'desain'      => 'bg-blue-100 text-blue-700',
                        'cetak'       => 'bg-purple-100 text-purple-700',
                    ];
                    $roleLabels = [
                        'super_admin' => 'Super Admin',
                        'admin'       => 'Admin',
                        'desain'      => 'Bagian Desain',
                        'cetak'       => 'Bagian Cetak',
                    ];
                    $isSelf = auth()->id() === $user->id;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors {{ !$user->is_active ? 'opacity-60' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full {{ $user->is_active ? 'bg-brand-100 text-brand-700' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                    @if($isSelf) <span class="text-xs text-brand-500 font-medium">Anda</span> @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $user->email }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleColors[$user->role] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $roleLabels[$user->role] ?? $user->role }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($user->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            @if(!$isSelf)
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('users.toggle-active', $user) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg transition-colors {{ $user->is_active ? 'text-slate-400 hover:text-orange-600 hover:bg-orange-50' : 'text-slate-400 hover:text-green-600 hover:bg-green-50' }}"
                                            title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($user->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-slate-400 px-3">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-16 text-center text-slate-400">Belum ada user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

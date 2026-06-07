@extends('layouts.app')
@section('title', isset($user) ? 'Edit User' : 'Tambah User')
@section('page-title', isset($user) ? 'Edit User' : 'Tambah User')
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="hover:text-brand-600">Manajemen User</a> / {{ isset($user) ? $user->name : 'Baru' }}
@endsection

@section('content')
<div class="max-w-xl">
    <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}" method="POST">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                <select name="role" required class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm bg-white">
                    @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'desain' => 'Bagian Desain', 'cetak' => 'Bagian Cetak'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('role', $user->role ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }} <span class="text-red-500">{{ isset($user) ? '' : '*' }}</span>
                </label>
                <input type="password" name="password" {{ isset($user) ? '' : 'required' }}
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-sm"
                       placeholder="{{ isset($user) ? 'Kosongkan jika tidak ingin ganti password' : 'Minimal 8 karakter' }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 outline-none text-sm">
            </div>

            @if(isset($user))
            <div class="flex items-center gap-3 py-2">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                </label>
                <span class="text-sm font-medium text-slate-700">Akun Aktif</span>
            </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                    {{ isset($user) ? 'Update User' : 'Buat User' }}
                </button>
                <a href="{{ route('users.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SMKN 9 Muaro Jambi') — Sistem Manajemen Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        sidebar: '#0f172a',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        
        /* ===== IMPROVED SIDEBAR STYLING ===== */
        /* Base sidebar link with high contrast */
        .sidebar-link { 
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            color: #e0f2fe;
            background: transparent;
            transition: all 150ms;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        
        /* Icon styling - better visibility */
        .sidebar-link svg { 
            flex-shrink: 0;
            color: #cbd5e1;
            transition: color 150ms;
            width: 1.25rem;
            height: 1.25rem;
        }
        
        .sidebar-link:hover {
            color: white;
            background-color: #1e293b;
        }
        
        .sidebar-link:hover svg {
            color: #e2e8f0;
        }
        
        .sidebar-link.active {
            color: white;
            background-color: #0284c7;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-link.active:hover {
            background-color: #0369a1;
        }
        
        .sidebar-link.active svg {
            color: white;
        }
        
        /* Section separators with better contrast */
        .sidebar-separator {
            padding-top: 1rem;
            padding-bottom: 0.5rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .sidebar-separator-text {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Status badges */
        .badge-status-pending       { background-color: #fef3c7; color: #92400e; }
        .badge-status-design_process { background-color: #dbeafe; color: #1e3a8a; }
        .badge-status-design_done   { background-color: #e0e7ff; color: #3730a3; }
        .badge-status-production    { background-color: #e9d5ff; color: #6b21a8; }
        .badge-status-done          { background-color: #dcfce7; color: #166534; }
        .badge-status-delivered     { background-color: #ccfbf1; color: #134e4a; }
        .badge-status-cancelled     { background-color: #fee2e2; color: #991b1b; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-sans" x-data="{ sidebarOpen: false }">

{{-- Mobile overlay --}}
<div class="fixed inset-0 bg-black/60 z-20 lg:hidden" x-show="sidebarOpen" @click="sidebarOpen=false" style="display:none"></div>

<div class="flex h-full">
    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-sidebar flex flex-col transform transition-transform duration-200 lg:relative lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-tight">SMKN 9</p>
                <p class="text-slate-400 text-xs">Muaro Jambi</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            @php $role = auth()->user()->role ?? ''; @endphp

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            @if(in_array($role, ['super_admin', 'admin']))
            {{-- Orders --}}
            <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Pesanan
            </a>

            {{-- Customers --}}
            <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Pelanggan
            </a>
            @endif

            @if(in_array($role, ['super_admin', 'admin', 'desain']))
            {{-- Design Tasks --}}
            <a href="{{ route('design-tasks.index') }}" class="sidebar-link {{ request()->routeIs('design-tasks.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Tugas Desain
            </a>
            @endif

            @if(in_array($role, ['super_admin', 'admin', 'cetak']))
            {{-- Production Tasks --}}
            <a href="{{ route('production-tasks.index') }}" class="sidebar-link {{ request()->routeIs('production-tasks.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
                Tugas Produksi
            </a>
            @endif

            @if(in_array($role, ['super_admin', 'admin']))
            {{-- Separator --}}
            <div class="sidebar-separator">
                <p class="sidebar-separator-text">Keuangan</p>
            </div>

            <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pengeluaran
            </a>
            @endif

            @if($role === 'super_admin')
            <div class="sidebar-separator">
                <p class="sidebar-separator-text">Pengaturan</p>
            </div>
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Manajemen User
            </a>
            @endif
        </nav>

        {{-- User info --}}
        <div class="px-3 py-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg">
                <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-500 text-xs truncate capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-red-400 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-slate-200 px-4 lg:px-6 py-3.5 flex items-center gap-4 shrink-0">
            <button @click="sidebarOpen=true" class="lg:hidden text-slate-500 hover:text-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex-1">
                <h1 class="text-slate-800 font-bold text-base lg:text-lg">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                <div class="text-xs text-slate-400 mt-0.5">@yield('breadcrumb')</div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-4 lg:mx-6 mt-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 lg:mx-6 mt-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm" x-data x-init="setTimeout(() => $el.remove(), 5000)">
            <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mx-4 lg:mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
            <p class="font-semibold mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@stack('scripts')
</body>
</html>

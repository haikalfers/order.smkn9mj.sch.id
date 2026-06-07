<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — SMKN 9 Muaro Jambi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: { brand: { 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' } }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans bg-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-7xl">
        <div class="flex items-center justify-center">
            
            {{-- ── LOGIN FORM (CENTERED) ──────────────────────────────────── --}}
            <div class="flex items-center justify-center">
                <div class="w-full max-w-md">
                    {{-- Card --}}
                    <div class="bg-white rounded-2xl shadow-2xl shadow-black/30 overflow-hidden">

                        {{-- Header stripe --}}
                        <div class="bg-gradient-to-r from-brand-700 to-brand-500 px-8 py-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-white font-extrabold text-xl leading-tight">Sistem Manajemen Produk</h1>
                                    <p class="text-blue-200 text-sm mt-0.5">SMKN 9 Muaro Jambi</p>
                                </div>
                            </div>
                        </div>

                        {{-- Form --}}
                        <div class="px-8 py-8">
                            <p class="text-slate-500 text-sm mb-6">Masuk dengan akun yang telah diberikan administrator.</p>

                            @if($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                                {{ $errors->first() }}
                            </div>
                            @endif
                            @if(session('error'))
                            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                                {{ session('error') }}
                            </div>
                            @endif

                            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                                @csrf

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm"
                                           placeholder="contoh@smkn9.sch.id">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                                    <input type="password" name="password" required
                                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm"
                                           placeholder="••••••••">
                                </div>

                                <button type="submit"
                                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-lg transition-colors text-sm">
                                    Masuk
                                </button>
                            </form>
                        </div>
                    </div>

                    <p class="text-center text-slate-600 text-xs mt-6">
                        © {{ date('Y') }} SMKN 9 Muaro Jambi · Sistem Manajemen Produk
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SHORTCUT BUTTON: TRACK ORDER ───────────────────────────────────── --}}
    <a href="{{ route('tracking.index') }}" class="fixed bottom-6 right-6 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all hover:scale-105 z-40">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="font-semibold">Lacak Pesanan</span>
    </a>
</body>
</html>

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Penggunaan di routes:
     *   ->middleware('role:admin,super_admin')
     *   ->middleware('role:desain')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Belum login → ke halaman login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super admin bypass semua pembatasan role
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Cek apakah role user cocok dengan yang diizinkan
        if (!$user->hasRole($roles)) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Akun non-aktif → logout & redirect login
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        return $next($request);
    }
}

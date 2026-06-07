<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = User::latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('users.create');
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(['super_admin', 'admin', 'desain', 'cetak'])],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', "Akun {$user->name} ({$user->role_label}) berhasil dibuat.");
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(User $user): View
    {
        $stats = [
            'total_orders'  => $user->orders()->count(),
            'active_orders' => $user->orders()->whereNotIn('status', ['done', 'delivered', 'cancelled'])->count(),
            'design_tasks'  => $user->designTasks()->count(),
            'prod_tasks'    => $user->productionTasks()->count(),
        ];

        return view('users.show', compact('user', 'stats'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(['super_admin', 'admin', 'desain', 'cetak'])],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Cegah super admin mengubah rolenya sendiri
        if ($user->id === auth()->id() && $validated['role'] !== 'super_admin') {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $updateData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    // ─── Toggle Active ────────────────────────────────────────────────────────

    public function toggleActive(User $user): RedirectResponse
    {
        // Cegah menonaktifkan akun sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(User $user): RedirectResponse
    {
        // Cegah hapus akun sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cegah hapus jika masih ada order aktif
        $activeOrders = $user->orders()
            ->whereNotIn('status', ['done', 'delivered', 'cancelled'])
            ->count();

        if ($activeOrders > 0) {
            return back()->with('error', "Akun tidak dapat dihapus karena masih memiliki {$activeOrders} order aktif.");
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Akun {$name} berhasil dihapus.");
    }
}

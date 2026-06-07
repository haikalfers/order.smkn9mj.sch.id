<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Customer::withCount('orders')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('customers.create');
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'email'   => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Customer $customer): View
    {
        $orders = $customer->orders()
            ->with('admin')
            ->latest()
            ->paginate(10);

        $stats = [
            'total'     => $customer->orders()->count(),
            'done'      => $customer->orders()->whereIn('status', ['done', 'delivered'])->count(),
            'total_revenue' => $customer->orders()->whereIn('status', ['done', 'delivered'])->sum('total_price'),
        ];

        return view('customers.show', compact('customer', 'orders', 'stats'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'email'   => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Customer $customer): RedirectResponse
    {
        // Cegah hapus jika ada order aktif
        $activeOrders = $customer->orders()
            ->whereNotIn('status', ['done', 'delivered', 'cancelled'])
            ->count();

        if ($activeOrders > 0) {
            return back()->with('error', "Pelanggan tidak dapat dihapus karena masih memiliki {$activeOrders} order aktif.");
        }

        $name = $customer->name;
        $customer->delete(); // soft delete

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan {$name} berhasil dihapus.");
    }
}

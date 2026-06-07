<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Standalone view - semua pengeluaran global
     */
    public function index(Request $request)
    {
        $query = Expense::with('order.customer')
                        ->orderByDesc('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month')) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);
            $query->whereYear('expense_date', $year)
                  ->whereMonth('expense_date', $month);
        }

        $expenses = $query->paginate(20);

        // Summary untuk cards
        $totalThisMonth = Expense::whereMonth('expense_date', now()->month)
                                 ->whereYear('expense_date', now()->year)
                                 ->sum('amount');

        $totalAll = Expense::sum('amount');

        $totalCount = Expense::whereMonth('expense_date', now()->month)
                             ->whereYear('expense_date', now()->year)
                             ->count();

        // Kategori terbesar bulan ini
        $topCat = Expense::whereMonth('expense_date', now()->month)
                         ->whereYear('expense_date', now()->year)
                         ->selectRaw('category, SUM(amount) as total')
                         ->groupBy('category')
                         ->orderByDesc('total')
                         ->first();

        $topCategory = $topCat 
            ? ucwords(str_replace('_', ' ', $topCat->category)) 
            : '—';

        return view('expenses.index', compact(
            'expenses', 'totalThisMonth', 'totalAll', 'totalCount', 'topCategory'
        ));
    }

    /**
     * Create form - nested di bawah order
     */
    public function create(Order $order)
    {
        return view('expenses.create', compact('order'));
    }

    /**
     * Store - nested di bawah order
     */
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'description'  => 'required|string|max:255',
            'category'     => 'required|in:bahan_baku,operasional,transportasi,lainnya',
            'expense_date' => 'required|date',
            'amount'       => 'required|numeric|min:0',
            'notes'        => 'nullable|string',
            'attachment_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $validated['recorded_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('attachment_path')) {
            $file = $request->file('attachment_path');
            $path = $file->store('expenses', 'public');
            $validated['attachment_path'] = $path;
        }

        $order->expenses()->create($validated);

        return redirect()->route('orders.show', $order)
                       ->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    /**
     * Edit form - nested di bawah order
     */
    public function edit(Order $order, Expense $expense)
    {
        return view('expenses.edit', compact('order', 'expense'));
    }

    /**
     * Update - nested di bawah order
     */
    public function update(Request $request, Order $order, Expense $expense)
    {
        $validated = $request->validate([
            'description'  => 'required|string|max:255',
            'category'     => 'required|in:bahan_baku,operasional,transportasi,lainnya',
            'expense_date' => 'required|date',
            'amount'       => 'required|numeric|min:0',
            'notes'        => 'nullable|string',
            'attachment_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Handle file upload
        if ($request->hasFile('attachment_path')) {
            // Delete old file if exists
            if ($expense->attachment_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->attachment_path);
            }
            
            $file = $request->file('attachment_path');
            $path = $file->store('expenses', 'public');
            $validated['attachment_path'] = $path;
        }

        $expense->update($validated);

        return redirect()->route('orders.show', $order)
                       ->with('success', 'Pengeluaran berhasil diupdate.');
    }

    /**
     * Destroy - nested di bawah order
     */
    public function destroy(Order $order, Expense $expense)
    {
        $expense->delete();

        return redirect()->route('orders.show', $order)
                       ->with('success', 'Pengeluaran berhasil dihapus.');
    }

    /**
     * Export expenses ke CSV
     */
    public function export(Request $request)
    {
        $query = Expense::with('order.customer')
                        ->orderByDesc('expense_date');

        // Terapkan filter yang sama seperti index
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month')) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);
            $query->whereYear('expense_date', $year)
                  ->whereMonth('expense_date', $month);
        }

        $expenses = $query->get();

        // Buat header CSV
        $csv = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Pengeluaran_' . date('Y-m-d_H-i-s') . '.csv"');

        // BOM untuk encoding UTF-8
        fputs($csv, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header kolom
        fputcsv($csv, ['Tanggal', 'No. Order', 'Pelanggan', 'Keterangan', 'Kategori', 'Jumlah'], ';');

        // Data rows
        foreach ($expenses as $exp) {
            fputcsv($csv, [
                $exp->expense_date->format('d/m/Y'),
                $exp->order?->order_number ?? '—',
                $exp->order?->customer->name ?? '—',
                $exp->description,
                ucwords(str_replace('_', ' ', $exp->category)),
                number_format($exp->amount, 0, ',', '.'),
            ], ';');
        }

        // Summary row
        fputcsv($csv, [], ';');
        fputcsv($csv, [
            'TOTAL',
            '',
            '',
            '',
            '',
            number_format($expenses->sum('amount'), 0, ',', '.'),
        ], ';');

        fclose($csv);
        exit;
    }
}

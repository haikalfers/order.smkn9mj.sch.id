<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductionTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionTask::with('order.customer', 'assignedUser')
                               ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->paginate(15);

        return view('production-tasks.index', compact('tasks'));
    }

    public function create()
    {
        $orders = Order::where('status', 'design_done')
                       ->with('customer')
                       ->get();

        return view('production-tasks.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'  => 'required|exists:orders,id',
            'user_id'   => 'nullable|exists:users,id',
            'status'    => 'required|in:waiting,in_progress,done',
            'notes'     => 'nullable|string',
        ]);

        ProductionTask::create($validated);

        return redirect()->route('production-tasks.index')
                       ->with('success', 'Task produksi berhasil dibuat.');
    }

    public function show(ProductionTask $productionTask)
    {
        $productionTask->load('order.customer', 'order.items', 'assignedUser', 'order.designTask');

        return view('production-tasks.show', ['task' => $productionTask]);
    }

    public function edit(ProductionTask $productionTask)
    {
        $productionTask->load('assignedUser');
        $orders = Order::where('status', '!=', 'cancelled')
                       ->with('customer')
                       ->get();
        $users = \App\Models\User::where('role', 'cetak')->get();

        return view('production-tasks.edit', [
            'task'   => $productionTask,
            'orders' => $orders,
            'users'  => $users,
        ]);
    }

    public function update(Request $request, ProductionTask $productionTask)
    {
        $validated = $request->validate([
            'status'   => 'required|in:waiting,in_progress,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes'    => 'nullable|string',
        ]);

        // Jika status menjadi 'done' dan progress tidak diisi, set ke 100
        if ($validated['status'] === 'done' && !($validated['progress'] ?? false)) {
            $validated['progress'] = 100;
        }

        $oldStatus = $productionTask->status;

        $updateData = $validated;
        if ($oldStatus !== 'in_progress' && $validated['status'] === 'in_progress') {
            $updateData['started_at'] = now();
        }
        if ($validated['status'] === 'done') {
            $updateData['finished_at'] = now();
        }

        $productionTask->update($updateData);

        // Side effect: jika status berubah ke 'done', update order ke 'done'
        if ($oldStatus !== 'done' && $validated['status'] === 'done') {
            $oldOrderStatus = $productionTask->order->status;
            $productionTask->order->update(['status' => 'done']);
            
            // Catat di status log
            $productionTask->order->statusLogs()->create([
                'from_status' => $oldOrderStatus,
                'to_status'   => 'done',
                'user_id'     => Auth::id(),
                'notes'       => 'Produksi selesai',
            ]);
        }

        return back()->with('success', 'Task produksi berhasil diupdate.');
    }

    public function destroy(ProductionTask $productionTask)
    {
        $productionTask->delete();

        return redirect()->route('production-tasks.index')
                       ->with('success', 'Task produksi berhasil dihapus.');
    }
}

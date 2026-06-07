<?php

namespace App\Http\Controllers;

use App\Models\DesignTask;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DesignTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = DesignTask::with('order.customer', 'assignedUser')
                           ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'null') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        $tasks = $query->paginate(15);
        $users = \App\Models\User::where('role', 'desain')->get();

        return view('design-tasks.index', compact('tasks', 'users'));
    }

    public function create()
    {
        $orders = Order::where('status', '!=', 'cancelled')
                       ->with('customer')
                       ->get();

        return view('design-tasks.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'  => 'required|exists:orders,id',
            'assigned_to'   => 'nullable|exists:users,id',
            'status'    => 'required|in:waiting,in_progress,revision,done',
            'notes'     => 'nullable|string',
        ]);

        DesignTask::create($validated);

        return redirect()->route('design-tasks.index')
                       ->with('success', 'Task desain berhasil dibuat.');
    }

    public function show(DesignTask $designTask)
    {
        $designTask->load('order.customer', 'order.items', 'assignedUser');

        return view('design-tasks.show', ['task' => $designTask]);
    }

    public function edit(DesignTask $designTask)
    {
        $designTask->load('assignedUser');
        $orders = Order::where('status', '!=', 'cancelled')
                        ->with('customer')
                        ->get();
        $users = \App\Models\User::where('role', 'desain')->get();

        return view('design-tasks.edit', [
            'task'   => $designTask,
            'orders' => $orders,
            'users'  => $users,
        ]);
    }

    public function update(Request $request, DesignTask $designTask)
    {
        $validated = $request->validate([
            'status'   => 'required|in:waiting,in_progress,revision,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes'    => 'nullable|string',
        ]);

        // Jika status menjadi 'done' dan progress tidak diisi, set ke 100
        if ($validated['status'] === 'done' && !($validated['progress'] ?? false)) {
            $validated['progress'] = 100;
        }

        $oldStatus = $designTask->status;
        $designTask->update($validated);

        // Side effect: jika status berubah ke 'done', update order ke 'design_done'
        if ($oldStatus !== 'done' && $validated['status'] === 'done') {
            $oldOrderStatus = $designTask->order->status;
            $designTask->order->update(['status' => 'design_done']);
            
            // Catat di status log
            $designTask->order->statusLogs()->create([
                'from_status' => $oldOrderStatus,
                'to_status'   => 'design_done',
                'user_id'     => Auth::id(),
                'notes'       => 'Desain selesai',
            ]);
        }

        return back()->with('success', 'Task desain berhasil diupdate.');
    }

    public function destroy(DesignTask $designTask)
    {
        $designTask->delete();

        return redirect()->route('design-tasks.index')
                       ->with('success', 'Task desain berhasil dihapus.');
    }

    /**
     * Bagian Desain mengambil task yang belum ditugaskan
     */
    public function assign(DesignTask $designTask)
    {
        if ($designTask->assigned_to) {
            return back()->with('error', 'Task ini sudah ditugaskan ke orang lain.');
        }

        $designTask->update([
            'assigned_to' => Auth::id(),
            'status'  => 'in_progress',
            'started_at' => now(),
        ]);

        return back()->with('success', 'Task berhasil diambil.');
    }

    /**
     * Admin assigns task ke desainer tertentu
     */
    public function adminAssign(Request $request, DesignTask $designTask)
    {
        if ($designTask->assigned_to) {
            return back()->with('error', 'Task ini sudah ditugaskan ke orang lain.');
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $designTask->update([
            'assigned_to' => $validated['assigned_to'],
            'status'  => 'in_progress',
            'started_at' => now(),
        ]);

        return back()->with('success', 'Task berhasil ditugaskan ke desainer.');
    }

    /**
     * Upload file hasil desain
     */
    public function uploadFile(Request $request, DesignTask $designTask)
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimes:ai,psd,pdf,zip,png,jpg,jpeg',
        ]);

        // Simpan ke storage/app/public/design-files/
        $path = $request->file('file')->store('design-files', 'public');

        // Ambil files yang sudah ada, tambah yang baru
        $files = $designTask->files ?? [];
        if (!is_array($files)) {
            $files = [];
        }
        $files[] = $path;

        $designTask->update(['files' => $files]);

        return back()->with('success', 'File desain berhasil diupload.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DesignTask;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionTask;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer', 'items', 'designTask', 'productionTask')
                      ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('order_number', 'like', "%$search%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%$search%");
                  });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('orders.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'deadline'       => 'nullable|date',
            'payment_status' => 'required|in:unpaid,dp,paid',
            'dp_amount'      => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'total_price'    => 'required|numeric|min:0',
            'items'          => 'required|array|min:1',
            'items.*.product_name'   => 'required|string',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit'           => 'nullable|string',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
            'items.*.design_file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,ai,psd|max:10240',
        ]);

        // Generate order number
        $lastOrder = Order::latest('id')->first();
        $nextNum = ($lastOrder?->id ?? 0) + 1;
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'order_number'    => $orderNumber,
            'customer_id'     => $validated['customer_id'],
            'admin_id'        => Auth::id(),
            'status'          => 'pending',
            'deadline'        => $validated['deadline'] ?? null,
            'payment_status'  => $validated['payment_status'],
            'dp_amount'       => $validated['dp_amount'] ?? 0,
            'notes'           => $validated['notes'] ?? null,
            'total_price'     => $validated['total_price'],
        ]);

        // Simpan items
        foreach ($validated['items'] as $index => $item) {
            $itemData = [
                'order_id'       => $order->id,
                'product_name'   => $item['product_name'],
                'quantity'       => $item['quantity'],
                'unit'           => $item['unit'] ?? 'pcs',
                'unit_price'     => $item['unit_price'],
                'specifications' => $item['specifications'] ? explode(',', $item['specifications']) : null,
            ];

            // Handle design file upload
            if ($request->hasFile("items.{$index}.design_file")) {
                $file = $request->file("items.{$index}.design_file");
                $path = $file->store('designs', 'public');
                $itemData['design_file'] = $path;
            }

            OrderItem::create($itemData);
        }

        // Catat di status log
        $order->statusLogs()->create([
            'from_status' => null,
            'to_status'   => 'pending',
            'user_id'     => Auth::id(),
            'notes'       => 'Order dibuat',
        ]);

        // Buat design task otomatis saat order dibuat
        DesignTask::create([
            'order_id'    => $order->id,
            'status'      => 'waiting',
            'assigned_to' => null,
        ]);

        return redirect()->route('orders.show', $order)
                       ->with('success', 'Pesanan berhasil dibuat.');
    }

    public function show(Order $order)
    {
        $order->load('customer', 'items', 'statusLogs.user', 'expenses', 'designTask.assignedUser', 'productionTask.assignedUser');

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        if (!in_array($order->status, ['pending', 'design_process'])) {
            return back()->with('error', 'Order hanya bisa diedit saat status pending atau design_process.');
        }

        $order->load('items');
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('orders.edit', compact('order', 'customers'));
    }

    public function update(Request $request, Order $order)
    {
        if (!in_array($order->status, ['pending', 'design_process'])) {
            return back()->with('error', 'Order hanya bisa diedit saat status pending atau design_process.');
        }

        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'deadline'       => 'nullable|date',
            'payment_status' => 'required|in:unpaid,dp,paid',
            'dp_amount'      => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'total_price'    => 'required|numeric|min:0',
            'items'          => 'required|array|min:1',
            'items.*.product_name'   => 'required|string',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit'           => 'nullable|string',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
            'items.*.design_file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,ai,psd|max:10240',
        ]);

        $order->update([
            'customer_id'    => $validated['customer_id'],
            'deadline'       => $validated['deadline'] ?? null,
            'payment_status' => $validated['payment_status'],
            'dp_amount'      => $validated['dp_amount'] ?? 0,
            'notes'          => $validated['notes'] ?? null,
            'total_price'    => $validated['total_price'],
        ]);

        // Hapus items lama, insert yang baru
        // Delete old design files
        foreach ($order->items as $oldItem) {
            if ($oldItem->design_file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldItem->design_file);
            }
        }
        $order->items()->delete();

        foreach ($validated['items'] as $index => $item) {
            $itemData = [
                'order_id'       => $order->id,
                'product_name'   => $item['product_name'],
                'quantity'       => $item['quantity'],
                'unit'           => $item['unit'] ?? 'pcs',
                'unit_price'     => $item['unit_price'],
                'specifications' => $item['specifications'] ? explode(',', $item['specifications']) : null,
            ];

            // Handle design file upload
            if ($request->hasFile("items.{$index}.design_file")) {
                $file = $request->file("items.{$index}.design_file");
                $path = $file->store('designs', 'public');
                $itemData['design_file'] = $path;
            }

            OrderItem::create($itemData);
        }

        return redirect()->route('orders.show', $order)
                       ->with('success', 'Pesanan berhasil diupdate.');
    }

    public function destroy(Order $order)
    {
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return back()->with('error', 'Order hanya bisa dihapus saat status pending atau cancelled.');
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('orders.index')
                       ->with('success', 'Pesanan berhasil dihapus.');
    }

    /**
     * Update status order — hanya bisa maju 1 langkah
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,design_process,design_done,production,done,delivered,cancelled',
            'notes'  => 'nullable|string',
            'design_assigned_to' => 'nullable|exists:users,id',
            'production_assigned_to' => 'nullable|exists:users,id',
        ]);

        $currentStatus = $order->status;
        $newStatus = $validated['status'];
        $notes = $validated['notes'] ?? null;

        // Validasi: status hanya bisa maju 1 langkah (atau langsung cancelled)
        $statusFlow = ['pending', 'design_process', 'design_done', 'production', 'done', 'delivered'];
        $currentIdx = array_search($currentStatus, $statusFlow);
        $newIdx = array_search($newStatus, $statusFlow);

        $isValidTransition = false;
        if ($newStatus === 'cancelled') {
            $isValidTransition = true; // bisa cancelled dari mana saja
        } elseif ($currentIdx !== false && $newIdx !== false && $newIdx === $currentIdx + 1) {
            $isValidTransition = true;
        }

        if (!$isValidTransition) {
            return back()->with('error', 'Transisi status tidak valid. Hanya bisa maju 1 langkah atau dibatalkan.');
        }

        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        // Catat di status log
        $order->statusLogs()->create([
            'from_status' => $oldStatus,
            'to_status'   => $newStatus,
            'user_id'     => Auth::id(),
            'notes'       => $notes,
        ]);

        // Side effects: buat/update design task & production task
        if ($newStatus === 'design_process' && !DesignTask::where('order_id', $order->id)->exists()) {
            $designTaskData = [
                'order_id' => $order->id,
                'status'   => $validated['design_assigned_to'] ? 'in_progress' : 'waiting',
            ];
            
            if ($validated['design_assigned_to']) {
                $designTaskData['assigned_to'] = $validated['design_assigned_to'];
                $designTaskData['started_at'] = now();
            }
            
            DesignTask::create($designTaskData);
        }

        if ($newStatus === 'production' && !ProductionTask::where('order_id', $order->id)->exists()) {
            $productionTaskData = [
                'order_id' => $order->id,
                'status'   => $validated['production_assigned_to'] ? 'in_progress' : 'waiting',
            ];
            
            if ($validated['production_assigned_to']) {
                $productionTaskData['assigned_to'] = $validated['production_assigned_to'];
                $productionTaskData['started_at'] = now();
            }
            
            ProductionTask::create($productionTaskData);
        }

        // Tandai task selesai jika order sudah diselesaikan
        if ($newStatus === 'done' && $order->productionTask) {
            $order->productionTask->update([
                'status'      => 'done',
                'finished_at' => now(),
            ]);
        }

        return back()->with('success', 'Status pesanan berhasil diupdate.');
    }

    /**
     * Print order
     */
    public function print(Order $order)
    {
        $order->load('customer', 'items');

        // Generate QR code untuk tracking (menggunakan SVG - tidak perlu GD extension)
        $trackingUrl = route('tracking.show', $order->order_number);
        $qrCode = new QrCode($trackingUrl);
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        
        // Convert SVG result ke data URI
        $svgContent = $result->getString();
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($svgContent);

        return view('orders.print', compact('order', 'qrCodeDataUri'));
    }
}

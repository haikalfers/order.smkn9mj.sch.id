<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Tampilkan halaman tracking untuk customer
     */
    public function index()
    {
        return view('tracking.index');
    }

    /**
     * Tampilkan halaman tracking dengan order number dari URL (untuk QR code)
     */
    public function show(string $orderNumber)
    {
        return view('tracking.index', [
            'preloadOrderNumber' => $orderNumber,
        ]);
    }

    /**
     * Search pesanan berdasarkan order number atau customer name
     * API endpoint untuk halaman tracking
     */
    public function search(Request $request)
    {
        $request->validate([
            'order_number' => 'nullable|string',
            'customer_name' => 'nullable|string',
        ]);

        $query = Order::whereNotIn('status', ['delivered', 'cancelled'])
                      ->with('customer', 'items', 'statusLogs');

        // Filter berdasarkan order number
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->order_number . '%');
        }

        // Filter berdasarkan customer name
        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function ($q) {
                $q->where('name', 'like', '%' . request('customer_name') . '%');
            });
        }

        $orders = $query->orderByDesc('created_at')->get();

        $data = $orders->map(function ($order) {
            $products = $order->items->pluck('product_name')->join(', ');
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name,
                'products' => $products,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $this->getPaymentStatusLabel($order->payment_status),
                'created_at' => $order->created_at->format('d M Y'),
                'total_price' => $order->total_price,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    /**
     * Tampilkan list pesanan yang aktif (belum terkirim)
     * API endpoint untuk public order list tracking
     */
    public function getActiveOrders()
    {
        $orders = Order::whereNotIn('status', ['delivered', 'cancelled'])
                       ->with('customer', 'items', 'statusLogs')
                       ->orderByDesc('created_at')
                       ->get();

        $data = $orders->map(function ($order) {
            $products = $order->items->pluck('product_name')->join(', ');
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name,
                'products' => $products,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $this->getPaymentStatusLabel($order->payment_status),
                'created_at' => $order->created_at->format('d M Y'),
                'total_price' => $order->total_price,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    /**
     * Tampilkan status tracking order berdasarkan order number
     * API endpoint untuk split screen di halaman login
     */
    public function getOrderStatus(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->order_number)
                      ->with('customer', 'statusLogs', 'items')
                      ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $this->getPaymentStatusLabel($order->payment_status),
                'created_at' => $order->created_at->format('d M Y'),
                'total_price' => $order->total_price,
                'products' => $order->items->pluck('product_name')->join(', '),
                'timeline' => $this->formatTimeline($order),
            ],
        ]);
    }

    /**
     * Format timeline status untuk display
     */
    private function formatTimeline(Order $order)
    {
        $statusSequence = [
            'pending' => ['label' => 'Pending', 'icon' => 'clock'],
            'design_process' => ['label' => 'Proses Desain', 'icon' => 'pencil'],
            'design_done' => ['label' => 'Desain Selesai', 'icon' => 'check'],
            'production' => ['label' => 'Produksi', 'icon' => 'cog'],
            'done' => ['label' => 'Selesai', 'icon' => 'check-double'],
            'delivered' => ['label' => 'Terkirim', 'icon' => 'truck'],
        ];

        $timeline = [];
        $currentReached = false;

        foreach ($statusSequence as $status => $info) {
            $completed = false;
            $active = false;
            $log = $order->statusLogs()->where('to_status', $status)->latest()->first();

            if ($status === $order->status) {
                $currentReached = true;
                $active = true;
                $completed = true;
            } elseif ($currentReached === false) {
                $completed = true;
            }

            $timeline[] = [
                'status' => $status,
                'label' => Order::STATUS_LABELS[$status] ?? $info['label'],
                'completed' => $completed,
                'active' => $active,
                'updated_at' => $log?->created_at?->format('d M Y H:i') ?? null,
            ];
        }

        return $timeline;
    }

    /**
     * Get label untuk payment status
     */
    private function getPaymentStatusLabel(string $status)
    {
        $labels = [
            'unpaid' => 'Belum Bayar',
            'dp' => 'DP (Pembayaran Awal)',
            'paid' => 'Lunas',
        ];

        return $labels[$status] ?? $status;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Store - Catat pembayaran baru untuk order
     */
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_date'  => 'required|date',
            'amount'        => 'required|numeric|min:0.01',
            'payment_type'  => 'required|in:dp,cicilan,pelunasan',
            'method'        => 'required|in:cash,transfer,cek,lainnya',
            'reference'     => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
        ]);

        $validated['order_id'] = $order->id;
        $validated['recorded_by'] = Auth::id();

        Payment::create($validated);

        // Update payment status order otomatis
        $this->updatePaymentStatus($order);

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    /**
     * Destroy - Hapus pembayaran
     */
    public function destroy(Order $order, Payment $payment)
    {
        // Hanya admin yang bisa hapus pembayaran (dijamin oleh middleware)
        $payment->delete();

        // Update payment status order otomatis
        $this->updatePaymentStatus($order);

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }

    /**
     * Helper: Update payment status order berdasarkan total pembayaran
     */
    private function updatePaymentStatus(Order $order)
    {
        $totalPaid = $order->payments()->sum('amount');
        $totalPrice = (float) $order->total_price;

        if ($totalPaid >= $totalPrice) {
            $order->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $order->update(['payment_status' => 'dp']);
        } else {
            $order->update(['payment_status' => 'unpaid']);
        }

        // Jika order selesai tapi pembayaran belum lunas, catat di log
        if ($order->status === 'delivered' && $order->payment_status !== 'paid') {
            $order->statusLogs()->create([
                'from_status' => $order->status,
                'to_status'   => $order->status,
                'user_id'     => Auth::id(),
                'notes'       => 'Pembayaran status diupdate ke: ' . $order->payment_status,
            ]);
        }
    }
}

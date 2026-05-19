<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List all orders with tab filtering.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product', 'payment'])->latest();

        // Filter by order type
        if ($type = $request->input('type')) {
            $query->where('order_type', $type);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15);

        // Counts for badges
        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processed' => Order::where('status', 'processed')->count(),
            'delivery' => Order::where('order_type', 'delivery')->count(),
            'dine_in' => Order::where('order_type', 'dine_in')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    /**
     * Show order detail.
     */
    public function show(Order $order)
    {
        $order->load(['user.profile', 'orderItems.product', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processed,delivered,completed,canceled'],
        ]);

        $order->load('payment');
        $newStatus = $validated['status'];

        if ($order->payment?->isManual()
            && ! $order->payment->isPaid()
            && in_array($newStatus, ['processed', 'delivered', 'completed'], true)) {
            return back()->with(
                'error',
                'Pembayaran transfer manual belum dikonfirmasi. Verifikasi pembayaran terlebih dahulu.'
            );
        }

        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        // If canceled, restore stock
        if ($validated['status'] === 'canceled' && $oldStatus !== 'canceled') {
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui!',
            ]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    /**
     * Langkah 1: Verifikasi pembayaran transfer manual (hanya ubah status bayar).
     */
    public function confirmPayment(Order $order)
    {
        $order->load('payment');

        if (! $order->payment?->isManual()) {
            return back()->with('error', 'Pesanan ini bukan pembayaran transfer manual.');
        }

        if ($order->payment->isPaid()) {
            return back()->with('warning', 'Pembayaran sudah dikonfirmasi sebelumnya.');
        }

        $order->payment->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with(
            'success',
            'Pembayaran dikonfirmasi. Klik "Mulai Menyiapkan Pesanan" untuk memulai masak.'
        );
    }

    /**
     * Langkah 2: Mulai menyiapkan pesanan (setelah pembayaran manual dikonfirmasi).
     */
    public function startProcessing(Order $order)
    {
        $order->load('payment');

        if (! $order->payment?->isManual()) {
            return back()->with('error', 'Pesanan ini bukan pembayaran transfer manual.');
        }

        if (! $order->payment->isPaid()) {
            return back()->with('error', 'Konfirmasi pembayaran terlebih dahulu sebelum mulai menyiapkan.');
        }

        if ($order->status !== 'pending') {
            return back()->with('warning', 'Status pesanan sudah di luar tahap menunggu persiapan.');
        }

        $order->update(['status' => 'processed']);

        return back()->with('success', 'Pesanan mulai disiapkan (sedang dimasak).');
    }
}

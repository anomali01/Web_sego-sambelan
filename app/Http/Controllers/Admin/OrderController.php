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

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

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
}

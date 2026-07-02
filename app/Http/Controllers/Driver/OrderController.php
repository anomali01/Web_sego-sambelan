<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of assigned orders for the driver.
     */
    public function index()
    {
        $orders = Order::where('driver_id', Auth::id())
            ->whereIn('status', ['processed', 'delivering', 'delivered']) // We show recent ones
            ->orderBy('created_at', 'desc')
            ->get();

        return view('driver.orders.index', compact('orders'));
    }

    /**
     * Display the specified assigned order.
     */
    public function show(Order $order)
    {
        // Ensure the order is assigned to this driver
        if ($order->driver_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        $order->load(['orderItems.product', 'user.profile']);

        return view('driver.orders.show', compact('order'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        if ($order->driver_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:delivering,delivered'],
            'delivery_proof' => ['required_if:status,delivered', 'image', 'max:5120'], // max 5MB
        ]);

        $data = ['status' => $request->status];

        if ($request->status === 'delivered' && $request->hasFile('delivery_proof')) {
            $path = $request->file('delivery_proof')->store('delivery_proofs', 'public');
            $data['delivery_proof'] = $path;
        }

        $order->update($data);

        $message = $request->status === 'delivering' 
            ? 'Status diperbarui: Sedang Diantar' 
            : 'Bukti pengantaran berhasil diunggah! Pesanan Selesai. Terima kasih atas kerja keras Anda.';

        return redirect()->route('driver.orders.show', $order->id)
            ->with('success', $message);
    }

    /**
     * Lightweight polling endpoint for smart auto-refresh.
     */
    public function poll()
    {
        $latestOrder = Order::where('driver_id', Auth::id())
            ->latest('updated_at')
            ->first();

        $activeCount = Order::where('driver_id', Auth::id())
            ->whereIn('status', ['processed', 'delivering'])
            ->count();

        return response()->json([
            'latest_at' => $latestOrder?->updated_at?->timestamp ?? 0,
            'active' => $activeCount,
        ]);
    }
}

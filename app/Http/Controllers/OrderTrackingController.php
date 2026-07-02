<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Show order tracking page.
     */
    public function show(Order $order, Request $request)
    {
        // Ensure buyer owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        $order->load(['orderItems.product', 'payment']);

        // Define status steps for timeline
        $statusSteps = [
            ['key' => 'pending', 'label' => 'Pesanan Dibuat', 'icon' => '📋'],
            ['key' => 'paid', 'label' => 'Pembayaran Diterima', 'icon' => '💳'],
            ['key' => 'processed', 'label' => 'Sedang Dimasak', 'icon' => '👨‍🍳'],
        ];

        if ($order->isDelivery()) {
            $statusSteps[] = ['key' => 'delivering', 'label' => 'Dalam Pengiriman', 'icon' => '🚚'];
        }

        $statusSteps[] = ['key' => 'completed', 'label' => 'Selesai', 'icon' => '✅'];

        // Determine current step index
        $currentStepIndex = $this->getCurrentStepIndex($order, $statusSteps);

        return view('orders.tracking', compact('order', 'statusSteps', 'currentStepIndex'));
    }

    /**
     * Show order history for the buyer.
     */
    public function history(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['orderItems.product', 'payment'])
            ->latest()
            ->paginate(10);

        return view('orders.history', compact('orders'));
    }

    /**
     * Get current step index for the status timeline.
     */
    private function getCurrentStepIndex(Order $order, array $steps): int
    {
        $isPaid = $order->payment && $order->payment->isPaid();

        if ($order->status === 'canceled') {
            return -1; // Special: canceled
        }

        if ($order->status === 'completed') {
            return count($steps) - 1;
        }

        // delivering = driver sedang mengantar
        if ($order->status === 'delivering') {
            $idx = array_search('delivering', array_column($steps, 'key'));
            return $idx !== false ? $idx : count($steps) - 2;
        }

        // delivered = driver sudah sampai, menunggu verifikasi admin → tampilkan juga di step delivering
        if ($order->status === 'delivered') {
            $idx = array_search('delivering', array_column($steps, 'key'));
            return $idx !== false ? $idx : count($steps) - 2;
        }

        if ($order->status === 'processed') {
            return array_search('processed', array_column($steps, 'key')) ?: 2;
        }

        if ($isPaid) {
            return 1; // Paid step
        }

        return 0; // Pending
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Handle Midtrans payment notification callback.
     * This endpoint is called by Midtrans server — no CSRF, no auth.
     */
    public function midtransCallback(Request $request, MidtransService $midtrans)
    {
        $notification = $request->all();

        // Verify signature
        if (!$midtrans->verifySignature($notification)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Find order
        $order = Order::where('order_number', $notification['order_id'])->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = $order->payment;

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Map Midtrans status
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? 'accept';

        switch ($transactionStatus) {
            case 'capture':
                if ($fraudStatus === 'accept') {
                    $this->markAsPaid($payment, $order, $notification);
                }
                break;

            case 'settlement':
                $this->markAsPaid($payment, $order, $notification);
                break;

            case 'pending':
                $payment->update([
                    'payment_status' => 'pending',
                    'gateway_response' => $notification,
                ]);
                break;

            case 'deny':
            case 'cancel':
                $this->markAsFailed($payment, $order, $notification);
                break;

            case 'expire':
                $payment->update([
                    'payment_status' => 'expired',
                    'gateway_response' => $notification,
                ]);
                $order->update(['status' => 'canceled']);
                $this->restoreStock($order);
                break;

            case 'refund':
            case 'partial_refund':
                $payment->update([
                    'payment_status' => 'refunded',
                    'gateway_response' => $notification,
                ]);
                break;
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Mark payment as paid and advance order status.
     */
    private function markAsPaid($payment, $order, array $notification): void
    {
        $payment->update([
            'payment_status' => 'paid',
            'payment_method' => $notification['payment_type'] ?? null,
            'transaction_id' => $notification['transaction_id'] ?? null,
            'gateway_response' => $notification,
            'paid_at' => now(),
        ]);

        // Auto-advance order to processed
        if ($order->status === 'pending') {
            $order->update(['status' => 'processed']);
        }
    }

    /**
     * Mark payment as failed and cancel order.
     */
    private function markAsFailed($payment, $order, array $notification): void
    {
        $payment->update([
            'payment_status' => 'failed',
            'gateway_response' => $notification,
        ]);

        $order->update(['status' => 'canceled']);
        $this->restoreStock($order);
    }

    /**
     * Restore product stock when order is canceled.
     */
    private function restoreStock($order): void
    {
        foreach ($order->orderItems as $item) {
            $item->product->increment('stock', $item->quantity);
        }
    }
}

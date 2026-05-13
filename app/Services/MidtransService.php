<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create a Snap token for the given order.
     */
    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_price,
            ],
            'item_details' => $order->orderItems->map(fn($item) => [
                'id' => (string) $item->product_id,
                'price' => (int) $item->unit_price,
                'quantity' => $item->quantity,
                'name' => Str::limit($item->product->name, 50),
            ])->toArray(),
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->profile->phone ?? '',
                'shipping_address' => $order->isDelivery() ? [
                    'first_name' => $order->user->name,
                    'phone' => $order->user->profile->phone ?? '',
                    'address' => $order->delivery_address,
                    'city' => $order->user->profile->city ?? '',
                    'postal_code' => $order->user->profile->postal_code ?? '',
                ] : null,
            ],
            'enabled_payments' => [
                'gopay', 'shopeepay',
                'bca_va', 'bni_va', 'bri_va', 'permata_va',
                'credit_card', 'qris',
            ],
            'callbacks' => [
                'finish' => url("/orders/{$order->id}/tracking"),
            ],
        ];

        // Remove null values
        $params['customer_details'] = array_filter($params['customer_details']);

        return \Midtrans\Snap::getSnapToken($params);
    }

    /**
     * Verify the signature from a Midtrans webhook notification.
     */
    public function verifySignature(array $notification): bool
    {
        $orderId = $notification['order_id'];
        $statusCode = $notification['status_code'];
        $grossAmount = $notification['gross_amount'];
        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return $expectedSignature === ($notification['signature_key'] ?? '');
    }
}

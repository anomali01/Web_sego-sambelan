<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StorePaymentSetting;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    /**
     * Show checkout page.
     */
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/cart')->with('warning', 'Keranjang Anda kosong.');
        }

        $total = collect($cart)->sum('subtotal');
        $profile = $request->user()->profile;
        $addresses = $request->user()->addresses()->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc')->get();
        $paymentSettings = StorePaymentSetting::current();

        return view('checkout.index', compact('cart', 'total', 'profile', 'addresses', 'paymentSettings'));
    }

    /**
     * Place an order (Midtrans or manual transfer).
     */
    public function placeOrder(Request $request, MidtransService $midtrans)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/cart')->with('warning', 'Keranjang Anda kosong.');
        }

        $validated = $request->validate([
            'order_type' => ['required', 'in:delivery,dine_in'],
            'address_id' => ['required_if:order_type,delivery', 'nullable', 'exists:user_addresses,id'],
            'table_number' => ['required_if:order_type,dine_in', 'nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_channel' => ['required', 'in:midtrans,manual'],
        ]);

        $settings = StorePaymentSetting::current();

        if ($validated['payment_channel'] === 'manual') {
            if (! $settings->manual_enabled || ! $settings->isConfigured()) {
                return back()->with('error', 'Pembayaran transfer manual belum diatur oleh penjual.');
            }
        }

        try {
            $order = DB::transaction(function () use ($cart, $validated, $request) {
                return $this->createOrderFromCart($cart, $validated, $request);
            });

            $paymentMethod = $validated['payment_channel'];
            $order->payment->update(['payment_method' => $paymentMethod]);

            session()->forget('cart');

            if ($paymentMethod === 'manual') {
                return redirect("/checkout/manual/{$order->id}")
                    ->with('info', 'Silakan transfer sesuai instruksi di bawah.');
            }

            $order->load(['orderItems.product', 'user.profile', 'payment']);
            $snapToken = $midtrans->createSnapToken($order);
            $order->payment->update(['snap_token' => $snapToken]);

            return redirect("/checkout/payment/{$order->id}");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show Midtrans Snap payment page.
     */
    public function showPayment(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['orderItems.product', 'payment']);

        if ($order->payment?->isManual()) {
            return redirect("/checkout/manual/{$order->id}");
        }

        return view('checkout.payment', [
            'order' => $order,
            'snapToken' => $order->payment->snap_token,
            'clientKey' => config('midtrans.client_key'),
            'snapUrl' => config('midtrans.snap_url'),
        ]);
    }

    /**
     * Show manual transfer instructions (bank + QRIS).
     */
    public function showManualPayment(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['orderItems.product', 'payment']);
        $settings = StorePaymentSetting::current();

        if (! $order->payment?->isManual()) {
            return redirect("/checkout/payment/{$order->id}");
        }

        return view('checkout.manual', compact('order', 'settings'));
    }

    /**
     * Upload transfer proof for manual payment.
     */
    public function uploadProof(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $payment = $order->payment;

        if (! $payment?->isManual() || $payment->isPaid()) {
            return redirect("/orders/{$order->id}/tracking");
        }

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:100'],
            'proof' => ['required', 'image', 'max:5120'],
        ]);

        if ($payment->proof_path) {
            Storage::disk('public')->delete($payment->proof_path);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');

        $payment->update([
            'sender_name' => $validated['sender_name'],
            'proof_path' => $path,
        ]);

        return redirect("/orders/{$order->id}/tracking")
            ->with('success', 'Bukti transfer terkirim. Menunggu konfirmasi penjual.');
    }

    /**
     * Create order, items, and pending payment from cart.
     */
    private function createOrderFromCart(array $cart, array $validated, Request $request): Order
    {
        $user = $request->user();
        $deliveryAddressString = null;
        $deliveryLatitude = null;
        $deliveryLongitude = null;

        if ($validated['order_type'] === 'delivery') {
            $userAddress = \App\Models\UserAddress::where('user_id', $user->id)
                ->where('id', $validated['address_id'])
                ->first();
            if ($userAddress) {
                $deliveryAddressString = $userAddress->full_address;
                $deliveryLatitude = $userAddress->latitude;
                $deliveryLongitude = $userAddress->longitude;
            } else {
                throw new \Exception("Alamat pengiriman tidak valid.");
            }
        }

        $totalPrice = 0;

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'order_type' => $validated['order_type'],
            'table_number' => $validated['table_number'] ?? null,
            'total_price' => 0,
            'status' => 'pending',
            'delivery_address' => $deliveryAddressString,
            'delivery_latitude' => $deliveryLatitude,
            'delivery_longitude' => $deliveryLongitude,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($cart as $productId => $item) {
            $product = Product::findOrFail($productId);

            if ($product->stock < $item['quantity']) {
                throw new \Exception("Stok {$product->name} tidak mencukupi.");
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $product->price * $item['quantity'],
            ]);

            $totalPrice += $product->price * $item['quantity'];
            $product->decrement('stock', $item['quantity']);
        }

        $order->update(['total_price' => $totalPrice]);

        Payment::create([
            'order_id' => $order->id,
            'payment_status' => 'pending',
            'amount' => $totalPrice,
        ]);

        return $order->fresh(['payment']);
    }

    /**
     * Store new address via AJAX
     */
    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'full_address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        // If it's the first address, make it primary
        $isPrimary = $request->user()->addresses()->count() === 0;

        $address = $request->user()->addresses()->create([
            'label' => $validated['label'],
            'full_address' => $validated['full_address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_primary' => $isPrimary,
        ]);

        return response()->json(['success' => true, 'address' => $address]);
    }

    /**
     * Cancel a pending order: restore stock, restore cart, delete order.
     */
    public function cancelOrder(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        // Only allow cancellation of pending orders that haven't been paid
        if ($order->status !== 'pending' || ($order->payment && $order->payment->isPaid())) {
            return redirect("/orders/{$order->id}/tracking")
                ->with('error', 'Pesanan ini tidak dapat dibatalkan karena sudah diproses.');
        }

        $order->load('orderItems.product');

        // Restore cart from order items
        $cart = [];
        foreach ($order->orderItems as $item) {
            // Restore stock
            $item->product->increment('stock', $item->quantity);

            // Rebuild cart session
            $cart[$item->product_id] = [
                'name' => $item->product->name,
                'price' => $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'image_url' => $item->product->image_url ?? null,
            ];
        }

        session()->put('cart', $cart);

        // Delete payment and order
        if ($order->payment) {
            $order->payment->delete();
        }
        $order->orderItems()->delete();
        $order->delete();

        return redirect('/cart')->with('info', 'Pesanan dibatalkan. Item sudah dikembalikan ke keranjang Anda.');
    }
}

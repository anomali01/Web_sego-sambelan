<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('checkout.index', compact('cart', 'total', 'profile'));
    }

    /**
     * Place an order and create Midtrans payment.
     */
    public function placeOrder(Request $request, MidtransService $midtrans)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/cart')->with('warning', 'Keranjang Anda kosong.');
        }

        $validated = $request->validate([
            'order_type' => ['required', 'in:delivery,dine_in'],
            'table_number' => ['required_if:order_type,dine_in', 'nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $validated, $request) {
                $user = $request->user();
                $totalPrice = 0;

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => Order::generateOrderNumber(),
                    'order_type' => $validated['order_type'],
                    'table_number' => $validated['table_number'] ?? null,
                    'total_price' => 0,
                    'status' => 'pending',
                    'delivery_address' => $validated['order_type'] === 'delivery'
                        ? $user->profile->full_address
                        : null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Create order items
                foreach ($cart as $productId => $item) {
                    $product = Product::findOrFail($productId);

                    // Check stock
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

                    // Deduct stock
                    $product->decrement('stock', $item['quantity']);
                }

                // Update total price
                $order->update(['total_price' => $totalPrice]);

                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_status' => 'pending',
                    'amount' => $totalPrice,
                ]);

                return $order;
            });

            // Generate Midtrans snap token
            $order->load(['orderItems.product', 'user.profile', 'payment']);
            $snapToken = $midtrans->createSnapToken($order);

            // Store snap token
            $order->payment->update(['snap_token' => $snapToken]);

            // Clear cart
            session()->forget('cart');

            return redirect("/checkout/payment/{$order->id}");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show payment page with Midtrans Snap.
     */
    public function showPayment(Order $order, Request $request)
    {
        // Ensure buyer owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['orderItems.product', 'payment']);

        return view('checkout.payment', [
            'order' => $order,
            'snapToken' => $order->payment->snap_token,
            'clientKey' => config('midtrans.client_key'),
            'snapUrl' => config('midtrans.snap_url'),
        ]);
    }
}

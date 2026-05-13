<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the cart.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum('subtotal');

        return view('cart.index', compact('cart', 'total'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->isInStock()) {
            return $this->respondWithError('Maaf, menu ini sedang tidak tersedia.');
        }

        $quantity = $request->input('quantity', 1);
        $cart = session()->get('cart', []);
        $productId = (string) $product->id;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
            $cart[$productId]['subtotal'] = $cart[$productId]['quantity'] * $cart[$productId]['price'];
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image_url' => $product->image_url,
                'subtotal' => $product->price * $quantity,
            ];
        }

        session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $product->name . ' ditambahkan ke keranjang!',
                'cart_count' => collect($cart)->sum('quantity'),
                'cart_total' => collect($cart)->sum('subtotal'),
            ]);
        }

        return back()->with('success', $product->name . ' ditambahkan ke keranjang!');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => ['required'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = session()->get('cart', []);
        $productId = (string) $request->product_id;

        if (!isset($cart[$productId])) {
            return $this->respondWithError('Item tidak ditemukan di keranjang.');
        }

        if ($request->quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $request->quantity;
            $cart[$productId]['subtotal'] = $cart[$productId]['price'] * $request->quantity;
        }

        session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart' => $cart,
                'cart_count' => collect($cart)->sum('quantity'),
                'cart_total' => collect($cart)->sum('subtotal'),
            ]);
        }

        return back()->with('success', 'Keranjang berhasil diperbarui!');
    }

    /**
     * Remove an item from cart.
     */
    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = (string) $request->product_id;

        unset($cart[$productId]);
        session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item dihapus dari keranjang.',
                'cart_count' => collect($cart)->sum('quantity'),
                'cart_total' => collect($cart)->sum('subtotal'),
            ]);
        }

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        session()->forget('cart');

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Keranjang dikosongkan.']);
        }

        return back()->with('success', 'Keranjang dikosongkan.');
    }

    /**
     * Return error response for AJAX or redirect.
     */
    private function respondWithError(string $message)
    {
        if (request()->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message);
    }
}

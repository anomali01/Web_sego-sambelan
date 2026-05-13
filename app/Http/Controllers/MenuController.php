<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display the catalog page with products.
     */
    public function index(Request $request)
    {
        $query = Product::where('is_available', true)->where('stock', '>', 0);

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $foodItems = $products->where('category', 'food');
        $drinkItems = $products->where('category', 'drink');

        return view('menu.index', compact('products', 'foodItems', 'drinkItems'));
    }
}

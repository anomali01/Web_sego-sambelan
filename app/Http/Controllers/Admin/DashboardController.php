<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_available', true)->count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),

            // Revenue Statistics (Today, Weekly, Monthly)
            'today_revenue' => Order::whereDate('created_at', $today)
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->sum('total_price'),

            'weekly_revenue' => Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->sum('total_price'),

            'monthly_revenue' => Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->sum('total_price'),

            // Cash Flow Breakdown by Payment Method
            'midtrans_revenue' => Payment::where('payment_status', 'paid')
                ->where(function ($q) {
                    $q->where('payment_method', 'midtrans')
                      ->orWhere('payment_method', 'gopay')
                      ->orWhere('payment_method', 'qris')
                      ->orWhere('payment_method', 'shopeepay')
                      ->orWhere('payment_method', 'bank_transfer'); // Midtrans subcategories if any
                })
                ->sum('amount'),

            'manual_revenue' => Payment::where('payment_status', 'paid')
                ->where('payment_method', 'manual')
                ->sum('amount'),

            'total_cash_inflow' => Payment::where('payment_status', 'paid')
                ->sum('amount'),
        ];

        // Fallback or adjust midtrans revenue in case payment_method has other gateway names
        $stats['midtrans_revenue'] = $stats['total_cash_inflow'] - $stats['manual_revenue'];

        $recentOrders = Order::with(['user', 'payment'])
            ->latest()
            ->take(10)
            ->get();

        // Cash Flow Details (Recent Successful Payments)
        $cashInflows = Payment::with(['order.user'])
            ->where('payment_status', 'paid')
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'cashInflows'));
    }
}

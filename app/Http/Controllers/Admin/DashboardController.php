<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $paid = Order::paid();

        $stats = [
            'revenue' => (clone $paid)->sum('total'),
            'revenue_month' => (clone $paid)->where('paid_at', '>=', now()->startOfMonth())->sum('total'),
            'card' => (clone $paid)->whereIn('payment_method', ['stripe', 'paymob'])->sum('total'),
            'orders' => Order::count(),
            'pending' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'customers' => Customer::count(),
            'products' => Product::count(),
            'low_stock' => Product::where('stock', '<=', 5)->count(),
            'messages' => ContactMessage::where('is_read', false)->count(),
        ];

        // Revenue of the last 14 days for the chart
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());
        $daily = Order::paid()->where('paid_at', '>=', $days->first())->get(['total', 'paid_at'])
            ->groupBy(fn ($o) => $o->paid_at->format('Y-m-d'))
            ->map->sum('total');

        $chart = [
            'labels' => $days->map->format('d M')->values(),
            'values' => $days->map(fn ($d) => round((float) ($daily[$d->format('Y-m-d')] ?? 0), 2))->values(),
        ];

        $methods = Order::paid()->selectRaw('payment_method, SUM(total) as total, COUNT(*) as count')
            ->groupBy('payment_method')->get()->keyBy('payment_method');

        $topProducts = OrderItem::whereHas('order', fn ($q) => $q->paid())
            ->selectRaw('product_name, SUM(quantity) as qty, SUM(total) as revenue')
            ->groupBy('product_name')->orderByDesc('qty')->take(5)->get();

        $recentOrders = Order::latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'methods', 'topProducts', 'recentOrders'));
    }
}

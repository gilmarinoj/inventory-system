<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function __invoke(): Factory|View|\Illuminate\View\View
    {
        $orders = Order::with(['items', 'payments'])->get();
        
        //Calculos de gastos
        $expenses_total = Purchase::sum('total_amount');
        $expenses_today = Purchase::whereDate('created_at', today())->sum('total_amount');

        return view('home', [
            'orders_count' => $orders->count(),
            'income' => $orders->sum(fn($order): float => min($order->receivedAmount(), $order->total())),
            'income_today' => $orders->where('created_at', '>=', today())
                ->sum(fn($order): float => min($order->receivedAmount(), $order->total())),
            'expenses_total' => $expenses_total,
            'expenses_today' => $expenses_today,
            'customers_count' => Customer::count(),
            'low_stock_products' => Product::lowStock()->get(),
            'best_selling_products' => Product::bestSelling()->get(),
            'current_month_products' => Product::currentMonthBestSelling()->get(),
            'past_months_products' => Product::pastMonthsHotProducts()->get(),
        ]);
    }
}

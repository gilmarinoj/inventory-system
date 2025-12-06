<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __invoke()
    {
        $now           = Carbon::now();
        $startOfMonth  = $now->copy()->startOfMonth();
        $today         = $now->copy()->startOfDay();

        // ================================
        // INGRESOS (Órdenes) – consultas directas a DB
        // ================================
        $income_total = Order::sumReceivedAmount(); // si tienes el scope, si no usa la línea de abajo
        // Si no tienes el scope sumReceivedAmount() genial, si no usa esta línea:
        // $income_total = Order::with('payments')->get()->sum(fn($o) => $o->receivedAmount());

        $income_month = Order::whereBetween('created_at', [$startOfMonth, $now])
            ->sumReceivedAmount(); // mismo caso

        $income_today = Order::whereDate('created_at', today())
            ->sumReceivedAmount();

        // ================================
        // GASTOS (Compras) – también directas a DB
        // ================================
        $expenses_total = Purchase::sum('total_amount');

        $expenses_month = Purchase::whereBetween('created_at', [$startOfMonth, $now])
            ->sum('total_amount');

        $expenses_today = Purchase::whereDate('created_at', today())
            ->sum('total_amount');

        // ================================
        // UTILIDAD
        // ================================
        $profit_total = $income_total - $expenses_total;
        $profit_month  = $income_month - $expenses_month;

        return view('home', [
            // Ingresos
            'income_total'    => $income_total,
            'income_month'    => $income_month,
            'income_today'    => $income_today,

            // Gastos
            'expenses_total'  => $expenses_total,
            'expenses_month'  => $expenses_month,
            'expenses_today'  => $expenses_today,

            // Ganancias
            'profit_total'    => $profit_total,
            'profit_month'    => $profit_month,

            // Otros datos
            'orders_count'           => Order::count(),
            'customers_count'        => Customer::count(),
            'low_stock_products'     => Product::lowStockDashboard(5)->get(),
            'best_selling_products'  => Product::bestSelling(5)->get(),
            'current_month_products' => Product::currentMonthBestSelling(5)->get(),
            'current_year_products'  => Product::currentYearBestSelling(5)->get(),
        ]);
    }
}

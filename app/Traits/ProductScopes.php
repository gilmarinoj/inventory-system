<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait ProductScopes
{
    /**
     * Check if product is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity < 10;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Scope for active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->where('quantity', '<', 10)->where('status', true);
    }

    /**
     * Scope for best selling products (total sold > 10).
     */
    public function scopeBestSelling($query, $limit = 5)
    {
        return $query->select('products.*')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->havingRaw('total_sold > 0')
            ->limit($limit);
    }

    /**
     * Scope for current month best selling products.
     */
    public function scopeCurrentMonthBestSelling($query, $limit = 5)
    {
        return $query->select('products.*')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereYear('orders.created_at', now()->year)
            ->whereMonth('orders.created_at', now()->month)
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->havingRaw('total_sold > 0')
            ->limit($limit);
    }

    /**
     * Scope for past months hot products (6 months).
     */
    public function scopeCurrentYearBestSelling($query, $limit = 5)
    {
        return $query->select('products.*')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereYear('orders.created_at', now()->year)
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->havingRaw('total_sold > 0')
            ->limit($limit);
    }

    public function scopeLowStockDashboard($query, $limit = 5)
    {
        return $query->where('quantity', '<', 3)
            ->where('status', true)
            ->orderBy('quantity')
            ->limit($limit);
    }

    public function scopeSearch($query, $term)
    {
        return $query->when($term, function ($query, $term): void {
            $query->where('name', 'LIKE', "%{$term}%");
        });
    }
}

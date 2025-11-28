<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property float $price
 * @property int $quantity
 * @property int $order_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @method static Builder<static>|OrderItem newModelQuery()
 * @method static Builder<static>|OrderItem newQuery()
 * @method static Builder<static>|OrderItem query()
 * @method static Builder<static>|OrderItem whereCreatedAt($value)
 * @method static Builder<static>|OrderItem whereId($value)
 * @method static Builder<static>|OrderItem whereOrderId($value)
 * @method static Builder<static>|OrderItem wherePrice($value)
 * @method static Builder<static>|OrderItem whereProductId($value)
 * @method static Builder<static>|OrderItem whereQuantity($value)
 * @method static Builder<static>|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItem extends Model
{
    protected $fillable = [
        'price',
        'quantity',
        'product_id',
        'order_id',
        'unit_price_usd',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'unit_price_usd' => 'float',
    ];

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get subtotal for this item.
     */
    public function subtotal(): float
    {
        return $this->price;
    }

    /**
     * Get unit price.
     */
    // PRECIO UNITARIO REAL de la venta (el que vale oro)
    public function unitPrice(): float
    {
        return (float) ($this->attributes['unit_price_usd'] ?? 0);
    }

    // Alias para que sea obvio en vistas y reportes
    public function getUnitPriceUsdAttribute(): float
    {
        // SIEMPRE devuelve un valor válido, nunca undefined
        return (float) ($this->attributes['unit_price_usd'] ?? 0);
    }

    public function getTotalUsdAttribute(): float
    {
        return (float) ($this->attributes['price'] ?? 0);
    }
}

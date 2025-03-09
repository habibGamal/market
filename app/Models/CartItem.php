<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'packets_quantity',
        'piece_quantity',
    ];

    protected $appends = [
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function getTotalAttribute(): float
    {
        return $this->product->packet_price * $this->packets_quantity + $this->product->piece_price * $this->piece_quantity;
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverReturnedProduct extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'driver_id',
        'product_id',
        'packets_quantity',
        'piece_quantity',
    ];

    /**
     * Get the driver that owns the returned product.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    /**
     * Get the product that was returned.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

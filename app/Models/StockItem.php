<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockItem extends Model
{

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPacketsQuantityAttribute(): float|null
    {
        if (!$this->relationLoaded('product')) {
            return null;
        }
        return $this->piece_quantity / $this->product->packet_to_piece;
    }

    public function getCostEvaluationAttribute(): float|null
    {
        return $this->packets_quantity === null ? null : $this->product->packet_cost * $this->packets_quantity;
    }

    public function getPriceEvaluationAttribute(): float|null
    {
        return $this->packets_quantity === null ? null : $this->product->packet_price * $this->packets_quantity;
    }
}

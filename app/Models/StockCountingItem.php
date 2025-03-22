<?php

namespace App\Models;

use App\Observers\StockCountingItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(StockCountingItemObserver::class)]
class StockCountingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'old_packets_quantity',
        'old_piece_quantity',
        'new_packets_quantity',
        'new_piece_quantity',
        'packet_cost',
        'stock_counting_id',
        'release_date',
        'total_diff',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function stockCounting(): BelongsTo
    {
        return $this->belongsTo(StockCounting::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductNameAttribute(): ?string
    {
        if ($this->relationLoaded('product')) {
            return $this->product->name;
        }

        return null;
    }

    public function getTotalOldQuantityByPieceAttribute(): int
    {
        if ($this->relationLoaded('product')) {
            return ($this->old_packets_quantity * $this->product->packet_to_piece) + $this->old_piece_quantity;
        }

        return 0;
    }

    public function getTotalNewQuantityByPieceAttribute(): int
    {
        if ($this->relationLoaded('product')) {
            return ($this->new_packets_quantity * $this->product->packet_to_piece) + $this->new_piece_quantity;
        }

        return 0;
    }

    public function getProductPacketToPieceAttribute(): int
    {
        if ($this->relationLoaded('product')) {
            return $this->product->packet_to_piece;
        }

        return 0;
    }
}

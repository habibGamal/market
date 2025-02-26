<?php

namespace App\Models;

use App\Observers\WasteItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(WasteItemObserver::class)]
class WasteItem extends Model
{
    use HasFactory;

    protected $casts = [
        'release_date' => 'date',
    ];

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductNameAttribute()
    {
        if ($this->relationLoaded('product')) {
            return $this->product->name;
        }

        return null;
    }

    public function getTotalQuantityByPieceAttribute()
    {
        if ($this->relationLoaded('product')) {
            return ($this->packets_quantity * $this->product->packet_to_piece) + $this->piece_quantity;
        }

        return 0;
    }

    public function getProductPacketToPieceAttribute()
    {
        if ($this->relationLoaded('product')) {
            return $this->product->name;
        }

        return null;
    }
}

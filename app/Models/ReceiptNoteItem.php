<?php

namespace App\Models;

use App\Observers\ReceiptNoteItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy([ReceiptNoteItemObserver::class])]
class ReceiptNoteItem extends Model
{
    /** @use HasFactory<\Database\Factories\ReceiptNoteItemFactory> */
    use HasFactory;

    protected $casts = [
        'reference_state' => 'array',
        'release_dates' => 'array',
    ];

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
        return $this->packets_quantity * $this->reference_state['product']['packet_to_piece'] + $this->piece_quantity;
    }

    public function getTotalQuantityByPacketAttribute()
    {
        return $this->packets_quantity + ($this->piece_quantity / $this->reference_state['product']['packet_to_piece']);
    }

    public function getQuantityReleasesAttribute()
    {
        if (count($this->release_dates) === 1) {
            return [
                $this->release_dates[0]['release_date'] => $this->totalQuantityByPiece,
            ];
        }

        return collect($this->release_dates)->mapWithKeys(function ($item) {
            return [$item['release_date'] => $item['piece_quantity']];
        })->toArray();
    }

    public function receiptNote()
    {
        return $this->belongsTo(ReceiptNote::class);
    }
}

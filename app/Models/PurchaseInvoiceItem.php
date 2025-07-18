<?php

namespace App\Models;

use App\Observers\PurchaseInvoiceItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(PurchaseInvoiceItemObserver::class)]
class PurchaseInvoiceItem extends Model
{

    /** @use HasFactory<\Database\Factories\PurchaseInvoiceItemFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'packets_quantity',
        'piece_quantity',
        'packet_cost',
        'total',
        'purchase_invoice_id',
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
}

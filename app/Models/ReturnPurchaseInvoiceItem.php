<?php

namespace App\Models;

use App\Observers\ReturnPurchaseInvoiceItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(ReturnPurchaseInvoiceItemObserver::class)]
class ReturnPurchaseInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'packets_quantity',
        'piece_quantity',
        'packet_cost',
        'release_date',
        'total',
        'return_purchase_invoice_id',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnPurchaseInvoice()
    {
        return $this->belongsTo(ReturnPurchaseInvoice::class);
    }

    public function getProductNameAttribute()
    {
        if ($this->relationLoaded('product')) {
            return $this->product->name;
        }
        return null;
    }
}

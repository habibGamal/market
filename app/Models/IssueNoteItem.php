<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueNoteItem extends Model
{

    /** @use HasFactory<\Database\Factories\PurchaseInvoiceItemFactory> */
    use HasFactory;

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

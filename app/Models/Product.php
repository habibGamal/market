<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'before_discount' => 'array',
        'limits' => 'array',
    ];

    public function getExpirationAttribute()
    {
        // Assuming 'expiration_duration' and 'expiration_unit' are columns in your products table
        $expirationDuration = $this->attributes['expiration_duration'];
        $expirationUnit = $this->attributes['expiration_unit'];

        return $expirationDuration . ' ' . __("general.period.$expirationUnit");
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'before_discount' => 'array',
    ];

    public function getExpirationAttribute()
    {
        // Assuming 'expiration_duration' and 'expiration_unit' are columns in your products table
        $expirationDuration = $this->attributes['expiration_duration'];
        $expirationUnit = $this->attributes['expiration_unit'];

        return $expirationDuration . ' ' . $expirationUnit;
    }

    public function setExpirationAttribute($value)
    {
        $parts = explode(' ', $value);
        $this->attributes['expiration_duration'] = $parts[0];
        $this->attributes['expiration_unit'] = $parts[1];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Brand>
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category>
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductLimit>
     */
    public function limits()
    {
        return $this->hasMany(ProductLimit::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('product')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المنتج");
    }
}

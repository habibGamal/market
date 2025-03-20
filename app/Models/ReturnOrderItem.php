<?php

namespace App\Models;

use App\Enums\ReturnOrderStatus;
use App\Observers\ReturnOrderItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ObservedBy([ReturnOrderItemObserver::class])]
class ReturnOrderItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'product_id',
        'packets_quantity',
        'packet_price',
        'packet_cost',
        'piece_quantity',
        'piece_price',
        'total',
        'profit',
        'driver_id',
        'return_reason',
        'notes',
        'status'
    ];

    protected $casts = [
        'status' => ReturnOrderStatus::class,
    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('return_order_item')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " عنصر المرتجع");
    }

}

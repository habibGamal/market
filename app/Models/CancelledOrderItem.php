<?php

namespace App\Models;

use App\Observers\CancelledOrderItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ObservedBy([CancelledOrderItemObserver::class])]
class CancelledOrderItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'packets_quantity',
        'packet_price',
        'packet_cost',
        'piece_quantity',
        'piece_price',
        'total',
        'profit',
        'officer_id',
        'order_id',
        'notes'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('cancelled_order_item')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " عنصر الملغي");
    }
}

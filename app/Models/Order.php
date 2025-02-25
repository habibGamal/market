<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function driver()
    {
        return $this->hasOneThrough(
            Driver::class,
            DriverTask::class,
            'order_id', // Foreign key on driver_tasks table
            'id', // Foreign key on drivers table
            'id', // Local key on orders table
            'driver_id' // Local key on driver_tasks table
        );
    }

    public function driverTask(): HasOne
    {
        return $this->hasOne(DriverTask::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cancelledItems(): HasMany
    {
        return $this->hasMany(CancelledOrderItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnOrderItem::class);
    }

    public function issueNote(): BelongsTo
    {
        return $this->belongsTo(IssueNote::class, 'issue_note_id');
    }

    public function scopeAssignableToDrivers($query)
    {
        return $query->whereDoesntHave('driverTask')
            ->whereNot('status', OrderStatus::DELIVERED)
            ->whereDate('created_at', '<', Carbon::today())
        ;
    }

    public function scopeNeedsIssueNote($query)
    {
        return $query->whereNull('issue_note_id')
            ->whereHas('driverTask')
            ->whereDate('created_at', '<', Carbon::today())
        ;
    }

    protected function netTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalReturns = $this->returnItems->sum('total');
                return $this->total - $totalReturns;
            }
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('order')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " الطلب");
    }
}

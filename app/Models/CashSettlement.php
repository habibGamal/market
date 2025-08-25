<?php

namespace App\Models;

use App\Enums\CashSettlementStatus;
use App\Enums\CashSettlementType;
use App\Observers\CashSettlementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ObservedBy([CashSettlementObserver::class])]
class CashSettlement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'value',
        'notes',
        'type',
        'officer_id',
        'status',
        'paid_at',
        'should_paid_at',
    ];

    protected $casts = [
        'type' => CashSettlementType::class,
        'status' => CashSettlementStatus::class,
        'paid_at' => 'datetime',
        'should_paid_at' => 'date',
        'value' => 'decimal:2',
    ];

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('cash_settlement')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " التسوية النقدية");
    }
}

<?php

namespace App\Models;

use App\Enums\BalanceOperation;
use App\Enums\DriverBalanceTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DriverBalanceTracker extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'driver_id',
        'transaction_type',
        'operation',
        'amount',
        'balance_before',
        'balance_after',
        'related_model_type',
        'related_model_id',
        'description',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_type' => DriverBalanceTransactionType::class,
        'operation' => BalanceOperation::class,
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function relatedModel(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('driver_balance_tracker')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " سجل حركة رصيد مندوب");
    }

    /**
     * Track a balance change for a driver
     */
    public static function track(
        int $driverId,
        DriverBalanceTransactionType $transactionType,
        BalanceOperation $operation,
        float $amount,
        ?Model $relatedModel = null,
        ?string $description = null,
        ?string $notes = null
    ): self {
        $driver = Driver::find($driverId);
        $balanceBefore = $driver->account->balance;

        return self::create([
            'driver_id' => $driverId,
            'transaction_type' => $transactionType,
            'operation' => $operation,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $operation === BalanceOperation::INCREMENT
                ? $balanceBefore + $amount
                : $balanceBefore - $amount,
            'related_model_type' => $relatedModel ? get_class($relatedModel) : null,
            'related_model_id' => $relatedModel?->id,
            'description' => $description,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }
}

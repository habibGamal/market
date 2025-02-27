<?php

namespace App\Models;

use App\Observers\AccountantReceiptNoteObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([AccountantReceiptNoteObserver::class])]
class AccountantReceiptNote extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'from_model_type',
        'from_model_id',
        'paid',
        'notes',
        'officer_id',
    ];

    protected $casts = [
        'paid' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('accountant_receipt_note')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " اذن قبض نقدية");
    }

    public function fromModel(): MorphTo
    {
        return $this->morphTo();
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}

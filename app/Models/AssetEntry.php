<?php

namespace App\Models;

use App\Observers\AssetEntryObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Services\VaultService;
use App\Services\WorkDayService;

#[ObservedBy([AssetEntryObserver::class])]
class AssetEntry extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'value',
        'officer_id',
        'notes',
    ];

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('asset_entry')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " الإضافة للخزينة");
    }
}

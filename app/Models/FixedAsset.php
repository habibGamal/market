<?php

namespace App\Models;

use App\Observers\FixedAssetObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([FixedAssetObserver::class])]
class FixedAsset extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'value',
        'notes',
        'accountant_id',
    ];

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('fixed_asset')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " الأصل الثابت");
    }
}

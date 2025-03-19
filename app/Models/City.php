<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class City extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'gov_id',
    ];

    public function gov(): BelongsTo
    {
        return $this->belongsTo(Gov::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('city')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المدينة");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Gov extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('gov')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المحافظة");
    }
}

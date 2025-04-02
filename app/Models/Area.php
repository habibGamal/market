<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Area extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'has_village',
        'city_id'
    ];

    protected $casts = [
        'has_village' => 'boolean'
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Customer::class);
    }

    /**
     * The users that belong to the area.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_area');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('area')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المنطقة");
    }
}

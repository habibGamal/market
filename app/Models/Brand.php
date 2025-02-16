<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(\App\Observers\ImageCleanupObserver::class)]
class Brand extends Model
{
    use HasFactory, LogsActivity;

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('brand')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " العلامة التجارية");
    }
}

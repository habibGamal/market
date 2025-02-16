<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ObservedBy(\App\Observers\ImageCleanupObserver::class)]
class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'image'];

    public function products()
    {
        return $this->hasMany(related: Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('category')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " الفئة");
    }
}

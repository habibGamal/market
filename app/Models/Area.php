<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Area extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['name'])
            ->useLogName('area')
            ->setDescriptionForEvent(fn(string $eventName) => "تم تعديل المنطقة: {$eventName}");
    }


}

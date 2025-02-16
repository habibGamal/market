<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Warehouse extends Model
{
    use HasFactory, LogsActivity;


    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('warehouse')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المخزن");
    }

    public static function getWarehouse()
    {
        return self::first();
    }
}

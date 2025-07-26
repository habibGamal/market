<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CashSettlementAccount extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'inlet_name_alias',
        'outlet_name_alias',
    ];

    public function cashSettlements(): HasMany
    {
        return $this->hasMany(CashSettlement::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('cash_settlement_account')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " حساب التسوية النقدية");
    }
}

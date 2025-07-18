<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExpenseType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'track',
    ];

    protected $casts = [
        'track' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function dailyExpenses(): HasMany
    {
        return $this->hasMany(Expense::class)->whereNotNull('approved_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('expense_type')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " نوع المصروف");
    }
}

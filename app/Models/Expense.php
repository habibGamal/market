<?php

namespace App\Models;

use App\Observers\ExpenseObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Services\VaultService;
use App\Services\WorkDayService;

#[ObservedBy([ExpenseObserver::class])]
class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'expense_type_id',
        'value',
        'notes',
        'approved_by',
        'accountant_id',
    ];

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('expense')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المصروف");
    }

    public function getApprovedAttribute(): bool
    {
        return !is_null($this->approved_by);
    }

    public function approve(){
        if($this->approved) return;
        \DB::transaction(function() {
            $this->update(['approved_by' => auth()->id()]);
            app(VaultService::class)->remove($this->value);
            app(WorkDayService::class)->update();
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\AccountantIssueNoteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([AccountantIssueNoteObserver::class])]
class AccountantIssueNote extends Model
{
    use HasFactory;

    protected $casts = [
        'paid' => 'decimal:2',
    ];

    protected $fillable = [
        'for_model_id',
        'for_model_type',
        'paid',
        'notes',
        'officer_id'
    ];

    public function forModel(): MorphTo
    {
        return $this->morphTo();
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}

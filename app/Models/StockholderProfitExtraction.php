<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\StockholderProfitExtractionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([StockholderProfitExtractionObserver::class])]
class StockholderProfitExtraction extends Model
{
    use HasFactory;

    protected $casts = [
        'profit' => 'decimal:2',
    ];

    protected $fillable = [
        'stockholder_id',
        'profit',
        'notes',
        'officer_id',
    ];

    public function stockholder(): BelongsTo
    {
        return $this->belongsTo(Stockholder::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}

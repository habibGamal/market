<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\StockCountingObserver;
use App\Traits\InvoiceHistory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(StockCountingObserver::class)]
class StockCounting extends Model
{
    use HasFactory, LogsActivity, InvoiceHistory;

    protected $fillable = [
        'total_diff',
        'status',
        'officer_id',
        'note',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('stock_counting')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " جرد المخزون");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'items' => $this->compareItems(['old_packets_quantity', 'old_piece_quantity', 'new_packets_quantity', 'new_piece_quantity', 'packet_cost']),
        ]);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountingItem::class);
    }

    public function getClosedAttribute(): bool
    {
        return $this->status === InvoiceStatus::CLOSED;
    }

    public function getRawStatusAttribute(): string
    {
        return $this->status->value;
    }

    public function printTemplate()
    {
        $this->loadMissing('items.product');
        return printTemplate()
            ->title('إذن جرد مخزون')
            ->info('رقم الإذن', $this->id)
            ->info('تاريخ الإذن', $this->created_at->format('Y-m-d h:i:s A'))
            ->info('تاريخ اخر تحديث', $this->updated_at->format('Y-m-d h:i:s A'))
            ->info('المسؤول', auth()->user()->name)
            ->total($this->total_diff)
            ->itemHeaders(['المنتج', 'الكمية القديمة (عبوات)', 'الكمية القديمة (قطع)', 'الكمية الجديدة (عبوات)', 'الكمية الجديدة (قطع)', 'السعر', 'الفرق', 'تاريخ الانتاج'])
            ->items($this->items->map(function ($item) {
                return [
                    $item->product->name,
                    $item->old_packets_quantity,
                    $item->old_piece_quantity,
                    $item->new_packets_quantity,
                    $item->new_piece_quantity,
                    $item->packet_cost,
                    $item->total_diff,
                    $item->release_date->format('Y-m-d'),
                ];
            })->toArray());
    }
}

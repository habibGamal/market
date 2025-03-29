<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\IssueNoteType;
use App\Traits\InvoiceHistory;
use App\Observers\IssueNoteObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([IssueNoteObserver::class])]
class IssueNote extends Model
{
    use LogsActivity, InvoiceHistory, HasFactory;

    protected $casts = [
        'total' => 'decimal:2',
        'status' => InvoiceStatus::class,
        'note_type' => IssueNoteType::class,
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('issue_note')
            ->setDescriptionForEvent(fn(string $eventName) => "تم {$eventName} اذن صرف");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'items' => $this->compareItems(['packets_quantity', 'piece_quantity', 'packet_cost', 'release_date']),
        ]);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IssueNoteItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function printTemplate()
    {
        $this->loadMissing('items.product');

        return printTemplate()
            ->title('اذن صرف')
            ->info('رقم الاذن', $this->id)
            ->info('تاريخ الاذن', $this->created_at->format('Y-m-d h:i:s A'))
            ->info('تاريخ اخر تحديث', $this->updated_at->format('Y-m-d h:i:s A'))
            ->info('المسؤول', auth()->user()->name)
            ->total($this->total)
            ->itemHeaders(['العلامة التجارية', 'المنتج', 'عدد العبوات', 'عدد القطع', 'تاريخ الانتاج'])
            ->items($this->items->map(function ($item) {
                return [
                    $item->product->brand->name,
                    $item->product->name,
                    $item->packets_quantity,
                    $item->piece_quantity,
                    $item->release_date,
                ];
            })->toArray());
    }

    public function getClosedAttribute()
    {
        return $this->status === InvoiceStatus::CLOSED;
    }

    public function getRawStatusAttribute()
    {
        return $this->status->value;
    }

    public function accountantReceiptNotes(): MorphMany
    {
        return $this->morphMany(AccountantReceiptNote::class, 'from_model');
    }

    public function scopeNeedAccountantReceiptNote($query)
    {
        return $query->where('note_type', IssueNoteType::RETURN_PURCHASES)
            ->where('status', InvoiceStatus::CLOSED)
            ->whereDoesntHave('accountantReceiptNotes');
    }
}

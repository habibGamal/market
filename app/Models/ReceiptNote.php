<?php

namespace App\Models;

use App\Enums\ReceiptNoteType;
use App\Observers\ReceiptNoteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use App\Enums\InvoiceStatus;
use App\Traits\InvoiceHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


#[ObservedBy([ReceiptNoteObserver::class])]
class ReceiptNote extends Model
{
    use LogsActivity, InvoiceHistory, HasFactory;

    protected $casts = [
        'status' => InvoiceStatus::class,
        'note_type' => ReceiptNoteType::class,
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('receipt_note')
            ->setDescriptionForEvent(fn(string $eventName) => "تم {$eventName} اذن استلام");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'items' => $this->compareItems(['packets_quantity', 'piece_quantity', 'packet_cost']),
        ]);
    }

    public function officer()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ReceiptNoteItem::class);
    }

    public function purchaseInvoice()
    {
        return $this->hasOne(PurchaseInvoice::class);
    }

    public function printTemplate()
    {
        $this->loadMissing('items.product');

        return printTemplate()
            ->title('اذن استلام')
            ->info('رقم الاذن', $this->id)
            ->info('تاريخ الاذن', $this->created_at->format('Y-m-d h:i:s A'))
            ->info('تاريخ اخر تحديث', $this->updated_at->format('Y-m-d h:i:s A'))
            ->info('المسؤول', auth()->user()->name)
            ->total($this->total)
            ->itemHeaders(['المنتج', 'عدد العبوات', 'عدد القطع'])
            ->items($this->items->map(function ($item) {
                return [
                    $item->product->name,
                    $item->packets_quantity,
                    $item->piece_quantity,
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
}

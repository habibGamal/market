<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\ReturnPurchaseInvoiceObserver;
use App\Traits\InvoiceHistory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(ReturnPurchaseInvoiceObserver::class)]
class ReturnPurchaseInvoice extends Model
{
    use LogsActivity, InvoiceHistory, HasFactory;

    protected $casts = [
        'status' => InvoiceStatus::class,
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('return_purchase_invoice')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " فاتورة مرتجع مشتريات");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'items' => $this->compareItems(['packets_quantity', 'piece_quantity', 'packet_cost']),
        ]);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnPurchaseInvoiceItem::class);
    }

    public function issueNote()
    {
        return $this->belongsTo(IssueNote::class);
    }

    public function getClosedAttribute()
    {
        return $this->status === InvoiceStatus::CLOSED;
    }

    public function getRawStatusAttribute()
    {
        return $this->status->value;
    }

    public function printTemplate()
    {
        $this->loadMissing('items.product');

        return printTemplate()
            ->title('فاتورة مرتجع مشتريات')
            ->info('رقم الفاتورة', $this->id)
            ->info('تاريخ الفاتورة', $this->created_at->format('Y-m-d h:i:s A'))
            ->info('تاريخ اخر تحديث', $this->updated_at->format('Y-m-d h:i:s A'))
            ->info('المسؤول', auth()->user()->name)
            ->total($this->total)
            ->itemHeaders(['المنتج', 'عدد العبوات', 'عدد القطع', 'السعر', 'الإجمالي', 'تاريخ الانتاج'])
            ->items($this->items->map(function ($item) {
                return [
                    $item->product->name,
                    $item->packets_quantity,
                    $item->piece_quantity,
                    $item->packet_cost,
                    $item->total,
                    $item->release_date->format('Y-m-d'),
                ];
            })->toArray());
    }
}

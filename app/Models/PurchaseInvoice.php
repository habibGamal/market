<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\PurchaseInvoiceObserver;
use App\Traits\InvoiceHistory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(PurchaseInvoiceObserver::class)]
class PurchaseInvoice extends Model
{
    use LogsActivity, InvoiceHistory, HasFactory;

    protected $fillable = [
        'total',
        'status',
        'notes',
        'officer_id',
        'receipt_note_id',
        'supplier_id',
        'execution_date',
        'payment_date',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('purchase_invoice')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " فاتورة الشراء");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'items' => $this->compareItems(['packets_quantity', 'packet_cost']),
        ]);
    }

    protected $casts = [
        'status' => InvoiceStatus::class,
        'execution_date' => 'date',
        'payment_date' => 'date',
    ];

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
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function receipt()
    {
        return $this->belongsTo(ReceiptNote::class, 'receipt_note_id');
    }

    public function printTemplate()
    {
        $this->loadMissing('items.product');

        return printTemplate()
            ->title('فاتورة شراء')
            ->info('رقم الفاتورة', $this->id)
            ->info('تاريخ الفاتورة', $this->created_at->format('Y-m-d h:i:s A'))
            ->info('تاريخ التنفيذ', $this->execution_date ? $this->execution_date->format('Y-m-d') : '-')
            ->info('تاريخ الدفع', $this->payment_date ? $this->payment_date->format('Y-m-d') : '-')
            ->info('تاريخ اخر تحديث', $this->updated_at->format('Y-m-d h:i:s A'))
            ->info('المسؤول', auth()->user()->name)
            ->total($this->total)
            ->itemHeaders(['المنتج', 'الكمية', 'السعر', 'الإجمالي'])
            ->items($this->items->map(function ($item) {
                return [
                    $item->product->name,
                    $item->packets_quantity,
                    $item->packet_cost,
                    $item->total,
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

    /**
     * Scope a query to only include closed purchase invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->where('status', InvoiceStatus::CLOSED);
    }

    public function scopeWithoutReceipt($query)
    {
        return $query->closed()->where('receipt_note_id', null);
    }
}

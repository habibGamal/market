<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\WasteObserver;
use App\Traits\InvoiceHistory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(WasteObserver::class)]
class Waste extends Model
{
    use LogsActivity, InvoiceHistory, HasFactory;

    protected $casts = [
        'status' => InvoiceStatus::class,
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('waste')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " هدر");
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

    public function items()
    {
        return $this->hasMany(WasteItem::class);
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
            ->title('إذن هدر')
            ->info('رقم الإذن', $this->id)
            ->info('تاريخ الإذن', $this->created_at->format('Y-m-d h:i:s A'))
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

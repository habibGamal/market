<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\InvoiceHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class IssueNote extends Model
{
    use LogsActivity, InvoiceHistory, HasFactory;

    protected $casts = [
        'status' => InvoiceStatus::class,
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


    public function officer()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(IssueNoteItem::class);
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
            ->itemHeaders(['المنتج', 'عدد العبوات', 'عدد القطع'])
            ->items($this->items->map(function ($item) {
                return [
                    $item->product->name,
                    $item->packets_quantity,
                    $item->piece_quantity,
                ];
            }));
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

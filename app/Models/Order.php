<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function driver()
    {
        return $this->hasOneThrough(
            Driver::class,
            DriverTask::class,
            'order_id', // Foreign key on driver_tasks table
            'id', // Foreign key on drivers table
            'id', // Local key on orders table
            'driver_id' // Local key on driver_tasks table
        );
    }

    public function driverTask(): HasOne
    {
        return $this->hasOne(DriverTask::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cancelledItems(): HasMany
    {
        return $this->hasMany(CancelledOrderItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnOrderItem::class);
    }

    public function issueNote(): BelongsTo
    {
        return $this->belongsTo(IssueNote::class, 'issue_note_id');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class)->withTimestamps();
    }

    public function scopeNotCancelled($query)
    {
        return $query->whereNot('status', OrderStatus::CANCELLED);
    }

    public function scopeAssignableToDrivers($query)
    {
        return $query->whereDoesntHave('driverTask')
            ->notCancelled()
            ->whereNot('status', OrderStatus::DELIVERED)
            ->whereDate('created_at', '<', Carbon::today())
        ;
    }

    public function scopeNeedsIssueNote($query)
    {
        return $query->whereNull('issue_note_id')
            ->notCancelled()
            ->whereHas('driverTask')
            ->whereDate('created_at', '<', Carbon::today())
        ;
    }

    public function getIsAssinalbeToDriverAttribute(): bool
    {
        return $this->status !== OrderStatus::DELIVERED &&
            $this->status !== OrderStatus::CANCELLED &&
            $this->created_at->startOfDay()->lt(now()->startOfDay());
    }

    public function getIsAbleToMakeIssueNoteAttribute(): bool
    {
        return is_null($this->issue_note_id) &&
            $this->status !== OrderStatus::CANCELLED &&
            $this->driverTask !== null &&
            $this->created_at->startOfDay()->lt(now()->startOfDay());
    }

    protected function netTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalReturns = $this->returnItems->sum('total');
                return $this->total - $totalReturns - $this->discount;
            }
        );
    }

    protected function profit(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalProfit = $this->items->sum('profit');
                return $totalProfit;
            }
        );
    }

    protected function netProfit(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalReturns = $this->returnItems->sum('profit');
                return $this->profit - $totalReturns - $this->discount;
            }
        );
    }

    /**
     * Calculate the net quantity for an order item after returns
     *
     * @param OrderItem $item The order item
     * @param array $returnedItems Collection of returned items grouped by product_id
     * @return array Contains 'netPieceQty', 'netPacketsQty', 'quantityDisplay', and 'netTotal'
     */
    protected function calculateNetQuantity($item, $returnedItems)
    {
        // Get returned quantities for this product (or zero if none)
        $returned = $returnedItems[$item->product_id] ?? ['piece_quantity' => 0, 'packets_quantity' => 0];

        // Calculate net quantities after returns
        $netPieceQty = max(0, $item->piece_quantity - $returned['piece_quantity']);
        $netPacketsQty = max(0, $item->packets_quantity - $returned['packets_quantity']);

        // Format quantity display
        $quantityDisplay = '';
        if ($netPacketsQty > 0) {
            $quantityDisplay .= $netPacketsQty . ' عبوة';
            if ($netPieceQty > 0) {
                $quantityDisplay .= "<br/>";
            }
        }
        if ($netPieceQty > 0) {
            $quantityDisplay .= $netPieceQty . ' قطعة';
        }

        // If everything was returned
        if ($netPieceQty == 0 && $netPacketsQty == 0) {
            $quantityDisplay = 'مرتجع بالكامل';
        }

        // Calculate the net total for this item after returns
        $netTotal = $item->total -
            ($returned['piece_quantity'] * $item->piece_price) -
            ($returned['packets_quantity'] * $item->packet_price);

        return [
            'netPieceQty' => $netPieceQty,
            'netPacketsQty' => $netPacketsQty,
            'quantityDisplay' => $quantityDisplay,
            'netTotal' => $netTotal
        ];
    }

    public function printTemplate()
    {
        $this->loadMissing('items.product', 'customer', 'returnItems');

        // Get returned items by product_id for easy lookup
        $returnedItems = $this->returnItems->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'piece_quantity' => $items->sum('piece_quantity'),
                    'packets_quantity' => $items->sum('packets_quantity')
                ];
            });

        $template = printTemplate()
            ->title('طلب')
            ->info('رقم الطلب', $this->id)
            ->info('تاريخ الطلب', $this->created_at->format('Y-m-d h:i:s A'))
            ->info('العميل', $this->customer->name)
            ->info('رقم الهاتف', $this->customer->phone)
            ->info('العنوان', $this->customer->address)
            ->info('الحالة', $this->status->getLabel())
            ->total($this->netTotal)
            ->itemHeaders(['المنتج', ['عبوات', 'سعر العبوة'], ['قطع', 'سعر القطعة'], 'الإجمالي'])
            ->items($this->items->map(function ($item) use ($returnedItems) {
                $netQuantity = $this->calculateNetQuantity($item, $returnedItems);

                return [
                    $item->product->name,
                    [
                        $netQuantity['netPacketsQty'],
                        $item->packet_price,
                    ],
                    [
                        $netQuantity['netPieceQty'],
                        $item->piece_price,
                    ],
                    $netQuantity['netTotal']
                ];
            })->toArray())
            ->layout58mm();


        return $template;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('order')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " الطلب");
    }
}

<?php

namespace App\Models;

use App\Enums\ExpirationUnit;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Laravel\Scout\Searchable;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    use HasFactory, LogsActivity, Searchable;

    protected $fillable = [
        'name',
        'image',
        'barcode',
        'description',
        'packet_cost',
        'packet_price',
        'piece_price',
        'packet_alter_name',
        'piece_alter_name',
        'expiration_duration',
        'expiration_unit',
        'before_discount',
        'packet_to_piece',
        'min_packets_stock_limit',
        'is_active',
        'can_sell_pieces',
        'brand_id',
        'category_id',
    ];

    protected $casts = [
        'before_discount' => 'array',
        'expiration_unit' => ExpirationUnit::class,
    ];

    protected $appends = [
        'packets_quantity',
        'expiration',
        'is_new',
        'is_deal',
        'prices',
        'has_stock',
    ];

    /**
     * Scope a query to only include products with active categories.
     */
    public function scopeWithActiveCategory($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope a query to only include products with active brands.
     */
    public function scopeWithActiveBrand($query)
    {
        return $query->whereHas('brand', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope a query to only include products with active category and brand.
     */
    public function scopeWithActiveRelations($query)
    {
        return $query->withActiveCategory()->withActiveBrand();
    }

    public function getPacketsQuantityAttribute()
    {
        if ($this->stock_items_sum_piece_quantity === null) return null;
        return $this->stock_items_sum_piece_quantity / $this->packet_to_piece;
    }

    public function getAvailablePiecesQuantityAttribute()
    {
        $this->loadMissing('stockItems');
        return $this->stockItems->sum('piece_quantity') - $this->stockItems->sum('unavailable_quantity') - $this->stockItems->sum('reserved_quantity');
    }

    public function getHasStockAttribute(): bool
    {
        return $this->getAvailablePiecesQuantityAttribute() > 0;
    }

    public function getExpirationAttribute()
    {
        // Assuming 'expiration_duration' and 'expiration_unit' are columns in your products table
        if (!isset($this->attributes['expiration_duration']) || !isset($this->attributes['expiration_unit'])) {
            return null;
        }
        $expirationDuration = $this->attributes['expiration_duration'];
        $expirationUnit = $this->attributes['expiration_unit'];

        return $expirationDuration . ' ' . $expirationUnit;
    }

    public function setExpirationAttribute($value)
    {
        $parts = explode(' ', $value);
        $this->attributes['expiration_duration'] = $parts[0];
        $this->attributes['expiration_unit'] = $parts[1];
    }

    public function getIsNewAttribute(): bool
    {
        return $this->created_at->diffInDays() < 7;
    }

    public function getIsDealAttribute(): bool
    {
        return $this->packet_price < $this->before_discount['packet_price'];
    }

    public function getPricesAttribute(): array
    {
        $packetOriginal = $this->before_discount['packet_price'] ?? null;
        $pieceOriginal = $this->before_discount['piece_price'] ?? null;

        // Only include original prices if they represent actual discounts
        return [
            'packet' => [
                'original' => ($packetOriginal !== null && $this->packet_price < $packetOriginal) ? $packetOriginal : null,
                'discounted' => $this->packet_price,
            ],
            'piece' => [
                'original' => ($pieceOriginal !== null && $this->piece_price < $pieceOriginal) ? round($pieceOriginal, 2) : null,
                'discounted' => $this->piece_price,
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Brand>
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category>
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductLimit>
     */
    public function limits()
    {
        return $this->hasMany(ProductLimit::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('product')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المنتج");
    }

    public function toSearchableArray()
    {
        // Load the category relationship
        $this->load('category:id,name');

        // Prepare the array to be indexed by TNTSearch
        $array = [
            'id' => $this->id,
            'name' => $this->normalizeArabicText($this->name),
            'original_name' => $this->name,
            'barcode' => $this->barcode ?? '',
            'category' => $this->category ? $this->category->name : '',
            'category_id' => $this->category_id,
        ];

        return $array;
    }

    private function normalizeArabicText(string $text): string
    {
        if (empty($text)) return '';

        $text = trim($text);

        // Remove tashkeel (diacritics)
        $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);

        // Normalize alef variations (أ, إ, آ -> ا)
        $text = preg_replace('/[أإآ]/u', 'ا', $text);

        // Normalize teh marbuta (ة -> ه)
        $text = preg_replace('/ة/u', 'ه', $text);

        return $text;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderItem>
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CancelledOrderItem>
     */
    public function cancelledOrderItems()
    {
        return $this->hasMany(CancelledOrderItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ReturnedOrderItem>
     */
    public function returnOrderItems()
    {
        return $this->hasMany(ReturnOrderItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CartItem>
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ReceiptNoteItem>
     */
    public function receiptNoteItems()
    {
        return $this->hasMany(ReceiptNoteItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ReturnPurchaseInvoiceItem>
     */
    public function returnPurchaseItems()
    {
        return $this->hasMany(ReturnPurchaseInvoiceItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<WasteItem>
     */
    public function wasteItems()
    {
        return $this->hasMany(WasteItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<StockCountingItem>
     */
    public function stockCountingItems()
    {
        return $this->hasMany(StockCountingItem::class);
    }

    public function isExpired(\Carbon\Carbon $releaseDate): bool
    {
        if (!isset($this->expiration_duration) || !isset($this->expiration_unit)) {
            return false;
        }

        $expirationDate = match($this->expiration_unit) {
            ExpirationUnit::DAY => $releaseDate->addDays($this->expiration_duration),
            ExpirationUnit::WEEK => $releaseDate->addWeeks($this->expiration_duration),
            ExpirationUnit::MONTH => $releaseDate->addMonths($this->expiration_duration),
            ExpirationUnit::YEAR => $releaseDate->addYears($this->expiration_duration),
        };

        return $expirationDate->isPast();
    }

    public function expirationDurationInDays(): ?int
    {
        if (!isset($this->expiration_duration) || !isset($this->expiration_unit)) {
            return null;
        }

        return match($this->expiration_unit) {
            ExpirationUnit::DAY => $this->expiration_duration,
            ExpirationUnit::WEEK => $this->expiration_duration * 7,
            ExpirationUnit::MONTH => $this->expiration_duration * 30,
            ExpirationUnit::YEAR => $this->expiration_duration * 365,
        };
    }
}

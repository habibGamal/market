<?php

namespace App\Models;

use App\Observers\DriverObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Filament\Panel;

#[ObservedBy([DriverObserver::class])]
class Driver extends User
{

    protected $table = 'users';

    public function account(): HasOne
    {
        return $this->hasOne(DriverAccount::class, 'driver_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(DriverTask::class, 'driver_id');
    }

    public function receipts(): BelongsToMany
    {
        return $this->belongsToMany(ReceiptNote::class, 'driver_receipts', 'driver_id', 'receipt_note_id')->withTimestamps();
    }

    public function returnedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'driver_returned_products', 'driver_id', 'product_id')
            ->withPivot('packets_quantity', 'piece_quantity')
            ->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'driver';
    }

    public function scopeDriversOnly($query)
    {
        return $query->whereHas('account', function ($query) {
            $query->whereNotNull('id');
        });
    }

    public function accountantReceiptNotes(): MorphMany
    {
        return $this->morphMany(AccountantReceiptNote::class, 'from_model');
    }
}

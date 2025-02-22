<?php

namespace App\Models;

use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'driver_assisment_officer_id',
        'order_id',
        'status',
    ];

    protected $casts = [
        'status' => DriverStatus::class,
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function assismentOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_assisment_officer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use NotificationChannels\WebPush\HasPushSubscriptions;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'location',
        'gov',
        'city',
        'village',
        'area_id',
        'address',
        'phone',
        'whatsapp',
        'email',
        'password',
        'business_type_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'blocked' => 'boolean',
        'rating_points' => 'integer',
        'postpaid_balance' => 'decimal:2',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

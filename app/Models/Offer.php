<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'instructions',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'instructions' => 'json',
        'is_active' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        return $this->is_active &&
               now()->between($this->start_at, $this->end_at);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withTimestamps();
    }
}

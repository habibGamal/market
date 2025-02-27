<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkDay extends Model
{
    protected $fillable = [
        'total_purchase',
        'total_sales',
        'total_expenses',
        'total_purchase_returnes',
        'total_day',
        'start_day',
        'day',
    ];

    protected $casts = [
        'total_purchase' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_purchase_returnes' => 'decimal:2',
        'total_day' => 'decimal:2',
        'start_day' => 'decimal:2',
        'day' => 'date',
    ];
}

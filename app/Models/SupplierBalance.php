<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBalance extends Model
{
    protected $table = 'supplier_balances';
    public $timestamps = false;
    protected $primaryKey = 'supplier_id';
    public $incrementing = false;

    protected $fillable = [
        'supplier_id',
        'receipt_total',
        'receipt_paid',
        'issue_total',
        'issue_paid',
        'balance',
    ];
}

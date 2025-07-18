<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Supplier extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'company_name',
    ];

    protected $appends = ['balance'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('supplier')
            ->setDescriptionForEvent(fn(string $eventName) => "تم " . __("general.events.$eventName") . " المورد");
    }

    // ReceiptNote relation through PurchaseInvoice
    public function receiptNotes()
    {
        return $this->hasManyThrough(
            \App\Models\ReceiptNote::class,
            \App\Models\PurchaseInvoice::class,
            'supplier_id',
            'id',
            'id',
            'receipt_note_id'
        );
    }

    // IssueNote relation through ReturnPurchaseInvoice
    public function issueNotes()
    {
        return $this->hasManyThrough(
            \App\Models\IssueNote::class,
            \App\Models\ReturnPurchaseInvoice::class,
            'supplier_id',
            'id',
            'id',
            'issue_note_id'
        );
    }


    public function getBalanceAttribute()
    {
        $balance = \DB::table('supplier_balances')->where('supplier_id', $this->id)->value('balance');
        return $balance ?? 0;
    }

    public function balanceView()
    {
        return $this->hasOne(SupplierBalance::class, 'supplier_id', 'id');
    }
}

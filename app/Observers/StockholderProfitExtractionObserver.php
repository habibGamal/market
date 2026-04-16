<?php

namespace App\Observers;

use App\Models\StockholderProfitExtraction;
use App\Services\VaultService;

class StockholderProfitExtractionObserver
{
    /**
     * Handle the StockholderProfitExtraction "creating" event.
     */
    public function creating(StockholderProfitExtraction $stockholderProfitExtraction): void
    {
        $stockholderProfitExtraction->officer_id = auth()->id();

        // Validate that profit amount is positive
        if ($stockholderProfitExtraction->profit <= 0) {
            throw new \InvalidArgumentException('مبلغ الأرباح يجب أن يكون أكبر من صفر');
        }
    }

    /**
     * Handle the StockholderProfitExtraction "created" event.
     */
    public function created(StockholderProfitExtraction $stockholderProfitExtraction): void
    {
        app(VaultService::class)->remove((float) $stockholderProfitExtraction->profit);
    }

    /**
     * Handle the StockholderProfitExtraction "deleted" event.
     */
    public function deleted(StockholderProfitExtraction $stockholderProfitExtraction): void
    {
        app(VaultService::class)->add((float) $stockholderProfitExtraction->profit);
    }
}

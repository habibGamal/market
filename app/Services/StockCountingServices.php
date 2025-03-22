<?php

namespace App\Services;

use App\Models\StockCounting;
use Illuminate\Support\Facades\DB;

class StockCountingServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {
    }

    /**
     * Process stock counting and adjust stock piece_quantity
     * This method is intentionally left empty as requested, to be implemented later
     *
     * @param StockCounting $stockCounting
     * @return void
     */
    public function processStockCounting(StockCounting $stockCounting): void
    {
        // Logic will be implemented later
    }

    /**
     * Delete a stock counting record
     *
     * @param StockCounting $stockCounting
     * @return void
     * @throws \Exception If the stock counting is closed
     */
    public function deleteStockCounting(StockCounting $stockCounting): void
    {
        if ($stockCounting->closed) {
            throw new \Exception('لا يمكن حذف عملية جرد مغلقة');
        }

        $stockCounting->deleteQuietly();
    }
}

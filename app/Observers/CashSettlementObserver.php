<?php

namespace App\Observers;

use App\Models\CashSettlement;
use App\Services\CashSettlementService;

class CashSettlementObserver
{
    public function __construct(
        private CashSettlementService $cashSettlementService
    ) {}

    /**
     * Handle the CashSettlement "created" event.
     */
    public function created(CashSettlement $cashSettlement): void
    {
        $this->cashSettlementService->handleCreation($cashSettlement);
    }

    /**
     * Handle the CashSettlement "updated" event.
     */
    public function updated(CashSettlement $cashSettlement): void
    {
        //
    }

    /**
     * Handle the CashSettlement "deleted" event.
     */
    public function deleted(CashSettlement $cashSettlement): void
    {
        $this->cashSettlementService->handleDeletion($cashSettlement);
    }

    /**
     * Handle the CashSettlement "restored" event.
     */
    public function restored(CashSettlement $cashSettlement): void
    {
        //
    }

    /**
     * Handle the CashSettlement "force deleted" event.
     */
    public function forceDeleted(CashSettlement $cashSettlement): void
    {
        $this->cashSettlementService->handleDeletion($cashSettlement);
    }
}

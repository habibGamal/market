<?php

namespace App\Services;

use App\Enums\CashSettlementStatus;
use App\Enums\CashSettlementType;
use App\Models\CashSettlement;

class CashSettlementService
{
    public function __construct(
        private VaultService $vaultService
    ) {}

    public function markAsPaid(CashSettlement $cashSettlement): void
    {
        if ($cashSettlement->status === CashSettlementStatus::PAID) {
            return;
        }

        \DB::transaction(function() use ($cashSettlement) {
            // Update the cash settlement status
            $cashSettlement->update([
                'status' => CashSettlementStatus::PAID,
                'paid_at' => now(),
            ]);

            // Update vault based on settlement type
            if ($cashSettlement->type === CashSettlementType::IN) {
                $this->vaultService->add((float) $cashSettlement->value);
            } else {
                $this->vaultService->remove((float) $cashSettlement->value);
            }
        });
    }

    public function markAsUnpaid(CashSettlement $cashSettlement): void
    {
        if ($cashSettlement->status === CashSettlementStatus::UNPAID) {
            return;
        }

        \DB::transaction(function() use ($cashSettlement) {
            // Update the cash settlement status
            $cashSettlement->update([
                'status' => CashSettlementStatus::UNPAID,
                'paid_at' => null,
            ]);

            // Reverse vault changes based on settlement type
            if ($cashSettlement->type === CashSettlementType::IN) {
                $this->vaultService->remove((float) $cashSettlement->value);
            } else {
                $this->vaultService->add((float) $cashSettlement->value);
            }
        });
    }

    public function handleDeletion(CashSettlement $cashSettlement): void
    {
        // If settlement was paid, reverse the vault changes
        if ($cashSettlement->status === CashSettlementStatus::PAID) {
            \DB::transaction(function() use ($cashSettlement) {
                if ($cashSettlement->type === CashSettlementType::IN) {
                    $this->vaultService->remove((float) $cashSettlement->value);
                } else {
                    $this->vaultService->add((float) $cashSettlement->value);
                }
            });
        }
    }
}

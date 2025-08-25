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
                // For revenue (IN): Add to vault when marked as paid
                $this->vaultService->add((float) $cashSettlement->value);
            } else {
                // For liability (OUT): Remove from vault when marked as paid (debt is settled)
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
                // For revenue (IN): Remove from vault when marked as unpaid
                $this->vaultService->remove((float) $cashSettlement->value);
            } else {
                // For liability (OUT): Add back to vault when marked as unpaid (debt is restored)
                $this->vaultService->add((float) $cashSettlement->value);
            }
        });
    }

    public function handleCreation(CashSettlement $cashSettlement): void
    {
        // Only liabilities (OUT) affect vault when created
        if ($cashSettlement->type === CashSettlementType::OUT && $cashSettlement->status === CashSettlementStatus::UNPAID) {
            \DB::transaction(function() use ($cashSettlement) {
                // Add liability to vault as it represents money we owe (debt)
                $this->vaultService->add((float) $cashSettlement->value);
            });
        }
        // Revenue (IN) doesn't affect vault until marked as paid
    }

    public function handleDeletion(CashSettlement $cashSettlement): void
    {
        \DB::transaction(function() use ($cashSettlement) {
            if ($cashSettlement->type === CashSettlementType::IN) {
                // For revenue (IN): Remove from vault only if it was paid
                if ($cashSettlement->status === CashSettlementStatus::PAID) {
                    $this->vaultService->remove((float) $cashSettlement->value);
                }
            } else {
                // For liability (OUT): Always remove from vault when deleted
                if ($cashSettlement->status === CashSettlementStatus::UNPAID) {
                    // Remove the unpaid liability from vault
                    $this->vaultService->remove((float) $cashSettlement->value);
                }
                // If liability was paid, no vault adjustment needed as it was already removed when paid
            }
        });
    }
}

<?php

namespace App\Services\Reports;

use App\Models\AssetEntry;
use App\Models\CashSettlement;
use App\Models\DriverAccount;
use App\Models\FixedAsset;
use App\Models\Order;
use App\Models\StockItem;
use App\Models\SupplierBalance;
use App\Models\Vault;
use App\Enums\CashSettlementType;
use App\Enums\CashSettlementStatus;
use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CentralCashFlowService
{
    /**
     * Get total stock cost evaluation
     */
    public function getStockCost(): float
    {
        return StockItem::query()
            ->join('products', 'stock_items.product_id', '=', 'products.id')
            ->select(DB::raw('SUM((stock_items.piece_quantity / products.packet_to_piece) * products.packet_cost) as total_cost'))
            ->value('total_cost') ?? 0;
    }

    /**
     * Get cost of orders in delivery status
     */
    public function getDeliveryOrdersCost(): float
    {
        return Order::query()
            ->where('status', OrderStatus::OUT_FOR_DELIVERY)
            ->with(['items.product'])
            ->get()
            ->sum(function ($order) {
                return $order->items->sum(function ($item) {
                    // Calculate cost: (packets_quantity * packet_cost) + ((piece_quantity / packet_to_piece) * packet_cost)
                    $packetsCost = $item->packets_quantity * $item->packet_cost;
                    $piecesCost = ($item->piece_quantity / $item->product->packet_to_piece) * $item->packet_cost;
                    return $packetsCost + $piecesCost;
                });
            }) ?? 0;
    }

    /**
     * Get current vault balance
     */
    public function getCurrentVaultBalance(): float
    {
        return Vault::first()->balance ?? 0;
    }

    /**
     * Get sum of all drivers balance
     */
    public function getDriversBalanceSum(): float
    {
        return DriverAccount::sum('balance') ?? 0;
    }

    /**
     * Get cash settlements with type IN (paid)
     */
    public function getCashSettlementsInPaid(): float
    {
        return CashSettlement::query()
            ->where('type', CashSettlementType::IN)
            ->where('status', CashSettlementStatus::PAID)
            ->sum('value') ?? 0;
    }

    /**
     * Get cash settlements with type IN (unpaid)
     */
    public function getCashSettlementsInUnpaid(): float
    {
        return CashSettlement::query()
            ->where('type', CashSettlementType::IN)
            ->where('status', CashSettlementStatus::UNPAID)
            ->sum('value') ?? 0;
    }

    /**
     * Get total supplier balances
     */
    public function getSuppliersBalanceSum(): float
    {
        return SupplierBalance::sum('balance') ?? 0;
    }

    /**
     * Get cash settlements with type OUT (paid)
     */
    public function getCashSettlementsOutPaid(): float
    {
        return CashSettlement::query()
            ->where('type', CashSettlementType::OUT)
            ->where('status', CashSettlementStatus::PAID)
            ->sum('value') ?? 0;
    }

    /**
     * Get cash settlements with type OUT (unpaid)
     */
    public function getCashSettlementsOutUnpaid(): float
    {
        return CashSettlement::query()
            ->where('type', CashSettlementType::OUT)
            ->where('status', CashSettlementStatus::UNPAID)
            ->sum('value') ?? 0;
    }

    /**
     * Get asset entries sum
     */
    public function getAssetEntriesSum(): float
    {
        return AssetEntry::sum('value') ?? 0;
    }

    /**
     * Get fixed assets sum
     */
    public function getFixedAssetsSum(): float
    {
        return FixedAsset::sum('value') ?? 0;
    }

    /**
     * Get all cash flow data for the report
     */
    public function getCashFlowData($startDate = null, $endDate = null): array
    {
        // Assets section
        $stockCost = $this->getStockCost();
        $deliveryOrdersCost = $this->getDeliveryOrdersCost();
        $vaultBalance = $this->getCurrentVaultBalance();
        $driversBalance = $this->getDriversBalanceSum();
        $cashSettlementsInPaid = $this->getCashSettlementsInPaid();
        $cashSettlementsInUnpaid = $this->getCashSettlementsInUnpaid();
        $fixedAssets = $this->getFixedAssetsSum();

        $totalAssets = $stockCost + $deliveryOrdersCost + $vaultBalance + $driversBalance + $cashSettlementsInUnpaid + $fixedAssets;

        // Responsibilities section
        $suppliersBalance = $this->getSuppliersBalanceSum();
        $cashSettlementsOutPaid = $this->getCashSettlementsOutPaid();
        $cashSettlementsOutUnpaid = $this->getCashSettlementsOutUnpaid();

        $totalResponsibilities = $suppliersBalance + $cashSettlementsOutUnpaid;

        // Asset entries (what we spent on assets)
        $assetEntries = $this->getAssetEntriesSum();

        // Final calculation: Assets - Responsibilities - Asset Entries
        $finalResult = $totalAssets - $totalResponsibilities - $assetEntries;

        return [
            'assets' => [
                'stock_cost' => $stockCost,
                'delivery_orders_cost' => $deliveryOrdersCost,
                'vault_balance' => $vaultBalance,
                'drivers_balance' => $driversBalance,
                'cash_settlements_in_paid' => $cashSettlementsInPaid,
                'cash_settlements_in_unpaid' => $cashSettlementsInUnpaid,
                'fixed_assets' => $fixedAssets,
                'total' => $totalAssets,
            ],
            'responsibilities' => [
                'suppliers_balance' => $suppliersBalance,
                'cash_settlements_out_paid' => $cashSettlementsOutPaid,
                'cash_settlements_out_unpaid' => $cashSettlementsOutUnpaid,
                'total' => $totalResponsibilities,
            ],
            'asset_entries' => $assetEntries,
            'final_result' => $finalResult,
            'is_profit' => $finalResult > 0,
        ];
    }
}

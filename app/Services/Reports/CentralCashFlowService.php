<?php

namespace App\Services\Reports;

use App\Models\AssetEntry;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\StockItem;
use App\Models\SupplierBalance;
use App\Models\Vault;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CentralCashFlowService
{
    /**
     * Get tracked expenses sum for the given period
     */
    public function getTrackedExpensesSum($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return Expense::query()
            ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
            ->where('expense_types.track', true)
            ->where('expenses.created_at', '>=', $startDate)
            ->where('expenses.created_at', '<=', $endDate)
            ->sum('expenses.value');
    }

    /**
     * Get current vault balance
     */
    public function getCurrentVaultBalance(): float
    {
        return Vault::first()->balance ?? 0;
    }

    /**
     * Get total stock items cost evaluation
     */
    public function getStockItemsCost(): float
    {
        return StockItem::query()
            ->join('products', 'stock_items.product_id', '=', 'products.id')
            ->select(DB::raw('SUM((stock_items.piece_quantity / products.packet_to_piece) * products.packet_cost) as total_cost'))
            ->value('total_cost') ?? 0;
    }

    /**
     * Get total suppliers balances
     */
    public function getSuppliersBalancesSum(): float
    {
        return SupplierBalance::sum('balance');
    }

    /**
     * Get asset entries sum for the given period
     */
    public function getAssetEntriesSum($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return AssetEntry::query()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('value');
    }

    /**
     * Get all cash flow data for the report
     */
    public function getCashFlowData($startDate = null, $endDate = null): array
    {
        $trackedExpenses = $this->getTrackedExpensesSum($startDate, $endDate);
        $vaultBalance = $this->getCurrentVaultBalance();
        $stockCost = $this->getStockItemsCost();
        $suppliersBalance = $this->getSuppliersBalancesSum();
        $assetEntries = $this->getAssetEntriesSum($startDate, $endDate);

        $totalAssets = $trackedExpenses + $vaultBalance + $stockCost;
        $totalResponsibilities = $suppliersBalance + $assetEntries;

        return [
            'assets' => [
                'tracked_expenses' => $trackedExpenses,
                'vault_balance' => $vaultBalance,
                'stock_cost' => $stockCost,
                'total' => $totalAssets,
            ],
            'responsibilities' => [
                'suppliers_balance' => $suppliersBalance,
                'asset_entries' => $assetEntries,
                'total' => $totalResponsibilities,
            ],
            'net_position' => $totalAssets - $totalResponsibilities,
        ];
    }
}

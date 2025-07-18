<?php

namespace App\Services\Reports;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\ReturnPurchaseInvoiceItem;
use App\Models\WasteItem;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockStateReportService
{
    /**
     * Get the total cost of available stock items
     *
     * @return float
     */
    public function getTotalAvailableStockCost(): float
    {
        return StockItem::query()
            ->join('products', 'stock_items.product_id', '=', 'products.id')
            ->select(DB::raw('SUM((stock_items.piece_quantity - stock_items.unavailable_quantity - stock_items.reserved_quantity) * products.packet_cost / products.packet_to_piece) as total_cost'))
            ->first()
            ->total_cost ?? 0;
    }

    /**
     * Get the total cost of unavailable stock items due to returns
     *
     * @return float
     */
    public function getTotalReturnedStockCost(): float
    {
        return ReturnPurchaseInvoiceItem::query()
            ->join('products', 'return_purchase_invoice_items.product_id', '=', 'products.id')
            ->join('return_purchase_invoices', 'return_purchase_invoice_items.return_purchase_invoice_id', '=', 'return_purchase_invoices.id')
            ->where('return_purchase_invoices.status', InvoiceStatus::CLOSED)
            ->whereNull('return_purchase_invoices.issue_note_id')
            ->select(DB::raw('SUM(return_purchase_invoice_items.total) as total_cost'))
            ->first()
            ->total_cost ?? 0;
    }

    /**
     * Get the total cost of unavailable stock items due to wastes
     *
     * @return float
     */
    public function getTotalWasteStockCost(): float
    {
        return WasteItem::query()
            ->join('products', 'waste_items.product_id', '=', 'products.id')
            ->join('wastes', 'waste_items.waste_id', '=', 'wastes.id')
            ->where('wastes.status', InvoiceStatus::CLOSED)
            ->whereNull('wastes.issue_note_id')
            ->select(DB::raw('SUM(waste_items.total) as total_cost'))
            ->first()
            ->total_cost ?? 0;
    }

    /**
     * Get query for products with their stock information
     *
     * @return Builder
     */
    public function getProductsWithStockInfo(): Builder
    {
        return Product::query()
            ->addSelect([
                'products.*',
                'available_stock' => StockItem::query()
                    ->selectRaw('SUM(stock_items.piece_quantity - stock_items.unavailable_quantity - stock_items.reserved_quantity)')
                    ->whereColumn('stock_items.product_id', 'products.id')
                    ->limit(1),
                'available_stock_cost' => StockItem::query()
                    ->selectRaw('SUM((stock_items.piece_quantity - stock_items.unavailable_quantity - stock_items.reserved_quantity) * p.packet_cost / p.packet_to_piece)')
                    ->whereColumn('stock_items.product_id', 'products.id')
                    ->join('products as p', 'p.id', '=', 'stock_items.product_id')
                    ->limit(1),
                'returned_stock' => ReturnPurchaseInvoiceItem::query()
                    ->selectRaw('SUM((return_purchase_invoice_items.packets_quantity * p.packet_to_piece) + return_purchase_invoice_items.piece_quantity)')
                    ->whereColumn('return_purchase_invoice_items.product_id', 'products.id')
                    ->join('products as p', 'p.id', '=', 'return_purchase_invoice_items.product_id')
                    ->join('return_purchase_invoices', 'return_purchase_invoice_items.return_purchase_invoice_id', '=', 'return_purchase_invoices.id')
                    ->where('return_purchase_invoices.status', InvoiceStatus::CLOSED)
                    ->whereNull('return_purchase_invoices.issue_note_id')
                    ->limit(1),
                'returned_stock_cost' => ReturnPurchaseInvoiceItem::query()
                    ->selectRaw('SUM(return_purchase_invoice_items.total)')
                    ->whereColumn('return_purchase_invoice_items.product_id', 'products.id')
                    ->join('return_purchase_invoices', 'return_purchase_invoice_items.return_purchase_invoice_id', '=', 'return_purchase_invoices.id')
                    ->where('return_purchase_invoices.status', InvoiceStatus::CLOSED)
                    ->whereNull('return_purchase_invoices.issue_note_id')
                    ->limit(1),
                'waste_stock' => WasteItem::query()
                    ->selectRaw('SUM(waste_items.packets_quantity * p.packet_to_piece + waste_items.piece_quantity)')
                    ->whereColumn('waste_items.product_id', 'products.id')
                    ->join('products as p', 'p.id', '=', 'waste_items.product_id')
                    ->join('wastes', 'waste_items.waste_id', '=', 'wastes.id')
                    ->where('wastes.status', InvoiceStatus::CLOSED)
                    ->whereNull('wastes.issue_note_id')
                    ->limit(1),
                'waste_stock_cost' => WasteItem::query()
                    ->selectRaw('SUM(waste_items.total)')
                    ->whereColumn('waste_items.product_id', 'products.id')
                    ->join('wastes', 'waste_items.waste_id', '=', 'wastes.id')
                    ->where('wastes.status', InvoiceStatus::CLOSED)
                    ->whereNull('wastes.issue_note_id')
                    ->limit(1),
            ])
            ->with(['brand', 'category'])
            ->withCasts([
                'available_stock' => 'integer',
                'available_stock_cost' => 'decimal:2',
                'returned_stock' => 'integer',
                'returned_stock_cost' => 'decimal:2',
                'waste_stock' => 'integer',
                'waste_stock_cost' => 'decimal:2',
            ]);
    }
}

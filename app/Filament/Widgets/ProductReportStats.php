<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ProductReportStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public $record;
    public $start;
    public $end;

    #[On('updateChart')]
    public function updateValues($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    protected function getStats(): array
    {
        if (empty($this->record)) {
            return $this->getEmptyStats();
        }

        $startDate = $this->start ? Carbon::parse($this->start) : now()->startOfMonth();
        $endDate = $this->end ? Carbon::parse($this->end) : now();



        return [
            Stat::make('إجمالي المشتريات', $this->getReceiptNoteItemsTotal($startDate, $endDate))
                ->description('إجمالي كمية المشتريات بالقطعة')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('success'),

            Stat::make('إجمالي المرتجعات', $this->getReturnOrderItemsTotal($startDate, $endDate))
                ->description('إجمالي كمية مرتجعات العملاء بالقطعة')
                ->descriptionIcon('heroicon-o-arrow-uturn-left')
                ->color('danger'),

            Stat::make('إجمالي طلبات البيع', $this->getOrderItemsTotal($startDate, $endDate))
                ->description('إجمالي كمية المبيعات بالقطعة')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('success'),


            Stat::make('إجمالي مرتجعات المشتريات', $this->getReturnPurchaseItemsTotal($startDate, $endDate))
                ->description('إجمالي كمية مرتجعات المشتريات بالقطعة')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('danger'),


            Stat::make('إجمالي الهالك', $this->getWasteItemsTotal($startDate, $endDate))
                ->description('إجمالي كمية الهالك بالقطعة')
                ->descriptionIcon('heroicon-o-trash')
                ->color('gray'),

            Stat::make('إجمالي الملغيات', $this->getCancelOrderItemsTotal($startDate, $endDate))
                ->description('إجمالي كمية الطلبات الملغاة بالقطعة')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('warning'),
        ];
    }


    private function getEmptyStats(): array
    {
        return [

        ];
    }

    private function getReturnOrderItemsTotal($startDate, $endDate): int
    {
        return $this->record->returnOrderItems()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->selectRaw('SUM(packets_quantity * ? + piece_quantity) as total_pieces', [$this->record->packet_to_piece])
            ->value('total_pieces') ?? 0;
    }

    private function getCancelOrderItemsTotal($startDate, $endDate): int
    {
        return $this->record->cancelledOrderItems()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->selectRaw('SUM(packets_quantity * ? + piece_quantity) as total_pieces', [$this->record->packet_to_piece])
            ->value('total_pieces') ?? 0;
    }

    private function getReceiptNoteItemsTotal($startDate, $endDate): int
    {
        return $this->record->receiptNoteItems()
            ->whereHas('receiptNote', function ($query) use ($startDate, $endDate) {
                $query->where('note_type', \App\Enums\ReceiptNoteType::PURCHASES)
                    ->where('status', \App\Enums\InvoiceStatus::CLOSED);

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->selectRaw('SUM(packets_quantity * ? + piece_quantity) as total_pieces', [$this->record->packet_to_piece])
            ->value('total_pieces') ?? 0;
    }

    private function getReturnPurchaseItemsTotal($startDate, $endDate): int
    {
        return $this->record->returnPurchaseItems()
            ->whereHas('returnPurchaseInvoice', function ($query) use ($startDate, $endDate) {
                $query->whereHas('issueNote', function ($query) {
                    $query->where('status', \App\Enums\InvoiceStatus::CLOSED);
                });

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->selectRaw('SUM((packets_quantity * ?) + piece_quantity) as total_pieces', [$this->record->packet_to_piece])
            ->value('total_pieces') ?? 0;
    }

    private function getOrderItemsTotal($startDate, $endDate): int
    {
        return $this->record->orderItems()
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->selectRaw('SUM(packets_quantity * ? + piece_quantity) as total_pieces', [$this->record->packet_to_piece])
            ->value('total_pieces') ?? 0;
    }

    private function getWasteItemsTotal($startDate, $endDate): int
    {
        return $this->record->wasteItems()
            ->whereHas('waste', function ($query) use ($startDate, $endDate) {
                $query->whereHas('issueNote', function ($query) use ($startDate, $endDate) {
                    $query->where('status', \App\Enums\InvoiceStatus::CLOSED);

                    if ($startDate && $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                });
            })
            ->selectRaw('SUM(packets_quantity * ? + piece_quantity) as total_pieces', [$this->record->packet_to_piece])
            ->value('total_pieces') ?? 0;
    }
}

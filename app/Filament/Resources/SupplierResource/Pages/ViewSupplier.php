<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Filament\Widgets\SupplierBalanceStats;
use App\Traits\ReportsFilter;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplier extends ViewRecord
{
    use ReportsFilter;

    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filter')
                ->label('تحديد الفترة')
                ->form(static::filtersForm())
                ->action(function (array $data): void {
                    if ($data['period'] !== static::PERIOD_CUSTOM) {
                        $range = static::getRange($data['period']);
                        $this->dispatch('updateSupplierStats', filterFormData: [
                            'start_date' => $range['start_date'],
                            'end_date' => $range['end_date'],
                            'period' => $data['period'],
                        ]);
                    } else {
                        $this->dispatch('updateSupplierStats', filterFormData: $data);
                    }
                }),
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SupplierBalanceStats::make(['supplierId' => $this->record->id]),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\PurchaseInvoicesRelationManager::class,
            RelationManagers\ReturnPurchaseInvoicesRelationManager::class,
        ];
    }
}

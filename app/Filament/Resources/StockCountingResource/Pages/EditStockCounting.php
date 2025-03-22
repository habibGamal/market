<?php

namespace App\Filament\Resources\StockCountingResource\Pages;

use App\Filament\Resources\StockCountingResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\StockCountingServices;

class EditStockCounting extends EditRecord
{
    use InvoiceLikeEditActions, InvoiceLikeEditCloseHandler;

    protected static string $resource = StockCountingResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try{
            \DB::transaction(function () use ($record, $data) {
                $record->update($data);
                if ($record->closed && $record->wasChanged('status')) {
                    app(StockCountingServices::class)->processStockCounting($record);
                }
            });
        }catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('خطأ في العملية')
                ->body($e->getMessage())
                ->send();
            $this->halt();
        }

        return $record;
    }
}

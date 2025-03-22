<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\WasteServices;

class EditWaste extends EditRecord
{
    use InvoiceLikeEditActions, InvoiceLikeEditCloseHandler;

    protected static string $resource = WasteResource::class;


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try{
            \DB::transaction(function () use ($record, $data) {
                $record->update($data);
                if ($record->closed && $record->wasChanged('status')) {
                    app(WasteServices::class)->processWaste($record);
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

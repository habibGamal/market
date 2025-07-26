<?php

namespace App\Filament\Resources\CashSettlementResource\Pages;

use App\Enums\CashSettlementStatus;
use App\Filament\Resources\CashSettlementResource;
use App\Services\CashSettlementService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCashSettlement extends CreateRecord
{
    protected static string $resource = CashSettlementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['officer_id'] = auth()->id();
        $data['status'] = CashSettlementStatus::UNPAID->value;
        return $data;
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label('إنشاء')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createAndPay')
                ->label('إنشاء ودفع')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('إنشاء ودفع التسوية')
                ->modalDescription('هل تريد إنشاء التسوية وتحديدها كمدفوعة مباشرة؟ سيتم تحديث الخزينة تلقائياً.')
                ->modalSubmitActionLabel('نعم، إنشاء ودفع')
                ->action(function () {
                    $data = $this->form->getState();
                    $data = $this->mutateFormDataBeforeCreate($data);

                    $record = $this->getModel()::create($data);

                    // Mark as paid using the service
                    app(CashSettlementService::class)->markAsPaid($record);

                    Notification::make()
                        ->title('تم إنشاء التسوية ودفعها بنجاح')
                        ->success()
                        ->send();

                    return $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء التسوية بنجاح';
    }
}

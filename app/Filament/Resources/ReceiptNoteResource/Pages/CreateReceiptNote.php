<?php

namespace App\Filament\Resources\ReceiptNoteResource\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Filament\Resources\ReceiptNoteResource;
use App\Filament\Traits\CreateAssignOfficer;
use App\Filament\Traits\InvoiceLikeCreateActions;
use App\Filament\Traits\InvoiceLikeCreateCloseHandler;
use App\Models\PurchaseInvoice;
use App\Models\Driver;
use App\Services\ReceiptNoteServices;
use Filament\Forms\Components\Select;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateReceiptNote extends CreateRecord
{
    use CreateAssignOfficer,InvoiceLikeCreateCloseHandler,InvoiceLikeCreateActions;
    protected static string $resource = ReceiptNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('from_purchase_invoice')
                ->label('استلام من فاتورة شراء')
                ->form([
                    Select::make('purchase_invoice_id')
                        ->label('فاتورة الشراء')
                        ->searchable()
                        ->options(function () {
                            return PurchaseInvoice::query()
                                ->withoutReceipt()
                                ->pluck('id', 'id')
                                ->toArray();
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    $purchaseInvoice = PurchaseInvoice::with('items.product:id,name,packet_to_piece')
                        ->findOrFail($data['purchase_invoice_id']);

                    $receipt = app(ReceiptNoteServices::class)
                        ->createFromPurchaseInvoice($purchaseInvoice);

                    redirect()->to(static::$resource::getUrl('edit', ['record' => $receipt->id]));
                }),

            Actions\Action::make('from_driver')
                ->label('استلام مرتجع من سائق')
                ->form([
                    Select::make('driver_id')
                        ->label('السائق')
                        ->searchable()
                        ->options(function () {
                            return Driver::query()
                                ->driversOnly()
                                ->whereHas('returnedProducts')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->required()
                ])
                ->action(function (array $data) {
                    $driver = Driver::with('returnedProducts')
                        ->findOrFail($data['driver_id']);

                    $receipt = app(ReceiptNoteServices::class)
                        ->createFromDriverReturns($driver);

                    redirect()->to(static::$resource::getUrl('edit', ['record' => $receipt->id]));
                })
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }
}

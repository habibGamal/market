<?php

namespace App\Filament\Resources\ReceiptNoteResource\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Filament\Resources\ReceiptNoteResource;
use App\Filament\Traits\CreateAssignOfficer;
use App\Filament\Traits\InvoiceLikeCreateActions;
use App\Filament\Traits\InvoiceLikeCreateCloseHandler;
use App\Models\PurchaseInvoice;
use Filament\Forms\Components\Select;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Set;

class CreateReceiptNote extends CreateRecord
{
    use CreateAssignOfficer,InvoiceLikeCreateCloseHandler,InvoiceLikeCreateActions;
    protected static string $resource = ReceiptNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('from_purchase_invoice')
                ->label('استلام من فاتورة شراء')
                ->form(
                    [
                        Select::make('purchase_invoice_id')
                            ->label('فاتورة الشراء')
                            ->options(
                                PurchaseInvoice::query()->withoutReceipt()
                                    ->pluck('id', 'id')
                            )
                            ->required(),
                    ]
                )->action(function (array $data) {
                    $purchaseInvoice = PurchaseInvoice::with('items.product:id,name,packet_to_piece')->findOrFail($data['purchase_invoice_id']);
                    $receipt = $purchaseInvoice->receipt()->create([
                        'note_type' => ReceiptNoteType::PURCHASES,
                        'status' => InvoiceStatus::DRAFT,
                        'total' => $purchaseInvoice->total,
                        'officer_id' => auth()->id(),
                    ]);
                    $purchaseInvoice->receipt()->associate($receipt)->save();
                    $receipt->items()->createMany(
                        $purchaseInvoice->items->map(function ($item) {
                            $item->piece_quantity = 0;
                            return [
                                'product_id' => $item->product_id,
                                'packets_quantity' => $item->packets_quantity,
                                'packet_cost' => $item->packet_cost,
                                'piece_quantity' => 0,
                                'release_dates' => [
                                    [
                                        'piece_quantity' => $item->packets_quantity * $item->product->packet_to_piece,
                                        'release_date' => now()->format('Y-m-d'),
                                    ]
                                ],
                                'reference_state' => $item->toArray(),
                                'total' => $item->total,
                            ];
                        })
                    );

                    $editRoute = static::$resource::getUrl('edit', ['record' => $receipt->id]);
                    redirect()->to($editRoute);
                })
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
        ]);
    }
}

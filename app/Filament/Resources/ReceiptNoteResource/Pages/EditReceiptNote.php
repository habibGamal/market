<?php

namespace App\Filament\Resources\ReceiptNoteResource\Pages;

use App\Filament\Resources\ReceiptNoteResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use App\Filament\Traits\InvoiceLikeTrackChanges;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditReceiptNote extends EditRecord
{
    use InvoiceLikeEditActions, InvoiceLikeEditCloseHandler, InvoiceLikeTrackChanges;
    protected static string $resource = ReceiptNoteResource::class;


    protected function afterValidate(): void
    {
        foreach ($this->data['items'] as $item) {
            if (count($item['release_dates']) === 1)
                continue;
            $sumPieceQuantity = array_sum(array_column($item['release_dates'], 'piece_quantity'));

            $packetsQuantity = $item['packets_quantity'];
            $packetToPiece = $item['reference_state']['product']['packet_to_piece'];
            $piecesQuantity = $item['piece_quantity'];

            $expectedPieceQuantity = $packetsQuantity * $packetToPiece + $piecesQuantity;

            if ($sumPieceQuantity !== $expectedPieceQuantity) {
                Notification::make()
                    ->title(
                        "خطأ في الكمية للمنتج: {$item['reference_state']['product']['name']}. الكمية المتوقعة لا تتطابق مع الكمية الفعلية في تواريخ الانتاج."
                    )
                    ->danger()
                    ->send();
                throw new Halt;
            }
        }
    }
}

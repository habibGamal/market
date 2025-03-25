<?php

namespace App\Filament\Actions\Tables;

use App\Enums\OrderStatus;
use App\Enums\ReturnOrderStatus;
use App\Services\OrderServices;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Support\Enums\MaxWidth;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Illuminate\Support\Collection;

class ReturnOrderItemsBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'return';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('إرجاع المحدد')
            ->color('warning')
            ->icon('heroicon-o-arrow-uturn-left')
            ->requiresConfirmation()
            ->modalHeading('إرجاع الأصناف المحددة')
            ->modalSubmitActionLabel('إرجاع')
            ->visible(fn() => $this->getLivewire()->getOwnerRecord()->status === OrderStatus::DELIVERED)
            ->fillForm(function ($data, Collection $records) {
                return [
                    'items' => $records->map(fn($item) => [
                        'item_id' => $item->id,
                        'product_name' => $item->product->name,
                        'packets_quantity' => $item->packets_quantity,
                        'piece_quantity' => $item->piece_quantity,
                    ])->toArray()
                ];
            })
            ->form([
                TableRepeater::make('items')
                    ->label('الأصناف')
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('عدد العبوات')->width('150px'),
                        Header::make('piece_quantity')->label('عدد القطع')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('item_id'),
                        Forms\Components\TextInput::make('product_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('piece_quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])
                    ->reorderable(false)
                    ->deletable(false)
                    ->addable(false),
                Forms\Components\Textarea::make('return_reason')
                    ->label('سبب الإرجاع')
                    ->required()
            ])
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->action(function (Collection $records, array $data) {
                $order = $this->getLivewire()->getOwnerRecord();
                $reason = $data['return_reason'];
                $itemsToReturn = collect($data['items'])->map(function ($item) use ($records, $reason) {
                    $orderItem = $records->firstWhere('id', $item['item_id']);
                    return [
                        'order_item' => $orderItem,
                        'product_id' => $orderItem->product_id,
                        'packets_quantity' => $item['packets_quantity'],
                        'packet_price' => $orderItem->packet_price,
                        'piece_quantity' => $item['piece_quantity'],
                        'piece_price' => $orderItem->piece_price,
                        'return_reason' => $reason,
                        'notes' => 'إرجاع جماعي',
                        'status' => 'pending'
                    ];
                })->toArray();

                try {
                    app(OrderServices::class)->returnItems($order, $itemsToReturn);
                    notifyCustomerWithReturnOrderStatus($order, ReturnOrderStatus::PENDING->value);
                    Notification::make()
                        ->title('تم إرجاع الأصناف بنجاح')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    $this->failureNotification(
                        Notification::make()
                            ->title('حدث خطأ أثناء إرجاع الأصناف')
                            ->body($e->getMessage())
                            ->danger()
                            ->send()
                    )->halt()->failure();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}

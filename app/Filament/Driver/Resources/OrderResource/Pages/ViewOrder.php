<?php

namespace App\Filament\Driver\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Driver\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Collection;
use App\Services\DriverServices;
use Filament\Actions;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('items_to_customer')
                ->label('تسليم الأصناف للعميل')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->fillForm(function ($record) {
                    return [
                        'items' => $record->items->map(fn($item) => [
                            'item_id' => $item->id,
                            'product_name' => $item->product->name,
                            'packets_quantity' => $item->packets_quantity,
                            'piece_quantity' => $item->piece_quantity,
                        ])->toArray()
                    ];
                })
                ->visible(fn($record) => $record->status === OrderStatus::OUT_FOR_DELIVERY)
                ->form(function ($record) {
                    $hasOffers = $record->offers()->exists();
                    return [
                        Repeater::make('items')
                            ->label('الأصناف المستلمة')
                            ->schema([
                                Forms\Components\Hidden::make('item_id'),
                                Forms\Components\TextInput::make('product_name')
                                    ->label('المنتج')
                                    ->disabled()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('packets_quantity')
                                    ->label('عدد العبوات المستلمة')
                                    ->numeric()
                                    ->required()
                                    ->minValue(fn($get, $record) => $hasOffers ? $record->items->find($get('item_id'))->packets_quantity : 0)
                                    ->maxValue(fn($get, $record) => $record->items->find($get('item_id'))->packets_quantity)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('piece_quantity')
                                    ->label('عدد القطع المستلمة')
                                    ->numeric()
                                    ->required()
                                    ->minValue(fn($get, $record) => $hasOffers ? $record->items->find($get('item_id'))->piece_quantity : 0)
                                    ->maxValue(fn($get, $record) => $record->items->find($get('item_id'))->piece_quantity)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->reorderable(false)
                            ->deletable(false)
                            ->addable(false)
                            ->reactive(),
                        Forms\Components\Placeholder::make('total')
                            ->label('إجمالي المستلم')
                            ->content(function (callable $get) {
                                $items = collect($get('items') ?? []);
                                try {
                                    return $items->sum(function ($item) {
                                        $itemModel = $this->getRecord()->items->find($item['item_id']);
                                        return (float)$item['packets_quantity'] * $itemModel->packet_price + (float)$item['piece_quantity'] * $itemModel->piece_price;
                                    });
                                } catch (\Exception $e) {
                                    return 'جاري حساب المجموع';
                                }
                            })
                            ->columnSpanFull(),
                    ];
                })
                ->action(function ($record, array $data, $action) {
                    try {
                        app(DriverServices::class)->deliverOrder($record, $record->items, $data['items']);
                        notifyCustomerWithOrderStatus($record->fresh());
                        Notification::make()
                            ->title('تم تسليم الأصناف وإرجاع الكميات المتبقية بنجاح')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        $action->failureNotification(
                            Notification::make()
                                ->title('حدث خطأ أثناء تسليم الأصناف')
                                ->body($e->getMessage())
                                ->danger()
                                ->send()
                        )->halt()->failure();
                    }
                })
                ->modalWidth(MaxWidth::Medium)
                ->requiresConfirmation()
                ->modalHeading('تسليم الأصناف للعميل')
                ->modalSubmitActionLabel('تسليم'),

            Action::make('deliver_all_items')
                ->label('تسليم جميع الأصناف')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(fn($record) => $record->status === OrderStatus::OUT_FOR_DELIVERY)
                ->action(function ($record, $action) {
                    try {
                        $itemsData = $record->items->map(fn($item) => [
                            'item_id' => $item->id,
                            'packets_quantity' => $item->packets_quantity,
                            'piece_quantity' => $item->piece_quantity,
                        ])->toArray();

                        app(DriverServices::class)->deliverOrder($record, $record->items, $itemsData);
                        notifyCustomerWithOrderStatus($record->fresh());

                        Notification::make()
                            ->title('تم تسليم جميع الأصناف بنجاح')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        $action->failureNotification(
                            Notification::make()
                                ->title('حدث خطأ أثناء تسليم الأصناف')
                                ->body($e->getMessage())
                                ->danger()
                                ->send()
                        )->halt()->failure();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('تسليم جميع الأصناف')
                ->modalDescription('هل أنت متأكد من تسليم جميع أصناف الطلب بالكامل؟')
                ->modalSubmitActionLabel('تسليم الكل'),

            Action::make('return_all_items')
                ->label('إرجاع جميع الأصناف')
                ->color('danger')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn($record) => $record->status === OrderStatus::OUT_FOR_DELIVERY)
                ->action(function ($record, $action) {
                    try {
                        app(DriverServices::class)->returnAllOrderItems($record);
                        notifyCustomerWithOrderStatus($record->fresh());
                        Notification::make()
                            ->title('تم إرجاع جميع الأصناف بنجاح')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        $action->failureNotification(
                            Notification::make()
                                ->title('حدث خطأ أثناء إرجاع الأصناف')
                                ->body($e->getMessage())
                                ->danger()
                                ->send()
                        )->halt()->failure();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('إرجاع جميع الأصناف')
                ->modalDescription('هل أنت متأكد من إرجاع جميع أصناف الطلب؟ لا يمكن التراجع عن هذا الإجراء.')
                ->modalSubmitActionLabel('إرجاع'),

            // printAction(Actions\Action::make('print')),
        ];
    }
}

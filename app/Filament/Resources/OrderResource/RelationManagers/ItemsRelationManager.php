<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Actions\Tables\CancelOrderItemsBulkAction;
use App\Filament\Actions\Tables\ReturnOrderItemsBulkAction;
use App\Notifications\Templates\OrderItemsCancelledTemplate;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use App\Services\OrderServices;
use App\Services\PlaceOrderServices;
use App\Services\NotificationService;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'الأصناف';
    protected static ?string $modelLabel = 'صنف';
    protected static ?string $pluralModelLabel = 'الأصناف';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج'),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات'),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع'),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP'),
            ])
            ->bulkActions([
                CancelOrderItemsBulkAction::make()
                    ->bindPage($this)
                    ->forceActionName('forceCancel')
                ,
                ReturnOrderItemsBulkAction::make(),
            ]);
    }


    public function cancelProcess($order, $itemsToCancel, $force = false)
    {
        \DB::transaction(function () use ($order, $itemsToCancel, $force) {
            app(OrderServices::class)->cancelledItems($order, $itemsToCancel);
            app(PlaceOrderServices::class)->orderEvaluation($order, skipValidation: $force);
        });

        app(NotificationService::class)->sendToUser(
            $order->customer,
            new OrderItemsCancelledTemplate,
            [
                'order_code' => $order->code,
                'items_count' => count($itemsToCancel),
                'order_id' => $order->id,
            ]
        );

        Notification::make()
            ->title('تم إلغاء الأصناف بنجاح')
            ->success()
            ->send();
    }

    public function forceCancelAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('force_cancel')
            ->requiresConfirmation()
            ->label('إلغاء الأصناف المحددة')
            ->modalDescription(fn(array $arguments) => $arguments['message'])
            ->action(function (\Filament\Actions\Action $action, array $arguments) {
                $this->cancelProcess(...$arguments['callback_arguments']);
            });
    }

}

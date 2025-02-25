<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Services\OrderServices;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;

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
                Tables\Actions\BulkAction::make('cancel')
                    ->label('إلغاء المحدد')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء الأصناف المحددة')
                    ->modalDescription('هل أنت متأكد من إلغاء الأصناف المحددة؟')
                    ->modalSubmitActionLabel('إلغاء')
                    ->visible(fn() => $this->getOwnerRecord()->status === OrderStatus::PENDING)
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
                    ->form(fn() => [
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
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->required()
                    ])
                    ->action(function (Collection $records, array $data) {
                        $order = $this->getOwnerRecord();
                        $note = $data['notes'];
                        $itemsToCancel = collect($data['items'])->map(function ($item) use ($records, $note) {
                            $orderItem = $records->firstWhere('id', $item['item_id']);
                            return [
                                'order_item' => $orderItem,
                                'product_id' => $orderItem->product_id,
                                'packets_quantity' => $item['packets_quantity'],
                                'packet_price' => $orderItem->packet_price,
                                'piece_quantity' => $item['piece_quantity'],
                                'piece_price' => $orderItem->piece_price,
                                'notes' => $note,
                            ];
                        })->toArray();

                        app(OrderServices::class)->cancelledItems($order, $itemsToCancel);

                        Notification::make()
                            ->title('تم إلغاء الأصناف بنجاح')
                            ->success()
                            ->send();
                    })
                    ->modalWidth(MaxWidth::FiveExtraLarge),

                Tables\Actions\BulkAction::make('return')
                    ->label('إرجاع المحدد')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalHeading('إرجاع الأصناف المحددة')
                    ->modalSubmitActionLabel('إرجاع')
                    ->visible(fn() => $this->getOwnerRecord()->status === OrderStatus::DELIVERED)
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
                    ->form(fn() => [
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
                    ->action(function (Collection $records, array $data, $action) {
                        $order = $this->getOwnerRecord();
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
                            Notification::make()
                                ->title('تم إرجاع الأصناف بنجاح')
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
                    ->modalWidth(MaxWidth::FiveExtraLarge),
            ]);
    }
}

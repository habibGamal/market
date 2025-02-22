<?php

namespace App\Filament\Driver\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use App\Services\OrderServices;
use App\Services\DriverServices;
use Illuminate\Support\Collection;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'الأصناف';
    protected static ?string $modelLabel = 'صنف';
    protected static ?string $pluralModelLabel = 'الأصناف';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make([
                    'default' => 2,
                    'sm' => 2,
                    'lg' => 4,
                ])->schema([
                            Tables\Columns\TextColumn::make('product.name')
                                ->label('المنتج')
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('total')
                                ->label('الإجمالي')
                                ->money('EGP')
                                ->formatSateUsingLabelPrefix()
                                ->color('green'),
                            Stack::make([
                                Tables\Columns\TextColumn::make('packets_quantity')
                                    ->label('عدد العبوات')
                                    ->formatSateUsingLabelPrefix(),
                                Tables\Columns\TextColumn::make('packet_price')
                                    ->label(label: 'سعر العبوة')
                                    ->money('EGP')
                                    ->formatSateUsingLabelPrefix()
                                    ->color('gray'),
                            ]),
                            Stack::make([
                                Tables\Columns\TextColumn::make('piece_quantity')
                                    ->label('عدد القطع')
                                    ->formatSateUsingLabelPrefix(),
                                Tables\Columns\TextColumn::make('piece_price')
                                    ->label('سعر القطعة')
                                    ->money('EGP')
                                    ->formatSateUsingLabelPrefix()
                                    ->color('gray'),
                            ]),
                        ])
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('items_to_customer')
                    ->label('تسليم الأصناف للعميل')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-uturn-left')
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
                                    ->minValue(0)
                                    ->maxValue(fn($get, $record) => $record->packets_quantity)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('piece_quantity')
                                    ->label('عدد القطع المستلمة')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(fn($get, $record) => $record->packets_quantity)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->reorderable(false)
                            ->deletable(false)
                            ->addable(false)
                    ])
                    ->action(function (Collection $records, array $data, $action) {
                        try {
                            $order = $this->getOwnerRecord();
                            app(DriverServices::class)->deliverOrder($order, $records, $data['items']);

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
            ]);
    }
}

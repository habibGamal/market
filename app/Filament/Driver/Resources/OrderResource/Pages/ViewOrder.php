<?php

namespace App\Filament\Driver\Resources\OrderResource\Pages;

use App\Filament\Driver\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Collection;
use App\Services\DriverServices;

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
                ->action(function ($record, array $data, $action) {
                    try {
                        app(DriverServices::class)->deliverOrder($record, $record->items, $data['items']);
                        
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
                ->modalSubmitActionLabel('تسليم')
        ];
    }
}

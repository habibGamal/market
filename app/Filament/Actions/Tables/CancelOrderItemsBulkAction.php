<?php

namespace App\Filament\Actions\Tables;

use App\Enums\OrderStatus;
use App\Services\OrderServices;
use App\Services\PlaceOrderServices;
use App\Services\NotificationService;
use App\Notifications\Templates\OrderItemsCancelledTemplate;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Support\Enums\MaxWidth;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Illuminate\Support\Collection;

class CancelOrderItemsBulkAction extends BulkAction
{
    protected array $failedActionArguments = [];
    protected ?\Closure $whenFailedCallback = null;

    public static function getDefaultName(): ?string
    {
        return 'cancel';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('إلغاء المحدد')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->requiresConfirmation()
            ->modalHeading('إلغاء الأصناف المحددة')
            ->modalDescription('هل أنت متأكد من إلغاء الأصناف المحددة؟')
            ->modalSubmitActionLabel('إلغاء الأصناف')
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
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->required()
            ])
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->action(function (Collection $records, array $data, $action) {
                $order = $this->getLivewire()->getOwnerRecord();
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

                try {
                    \DB::transaction(function () use ($order, $itemsToCancel) {
                        app(OrderServices::class)->cancelledItems($order, $itemsToCancel);
                        // app(PlaceOrderServices::class)->orderValidationAndEvaluation($order);
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
                } catch (\Exception $e) {
                    // Show confirmation dialog for validation errors

                    $this->failedActionArguments = [
                        'order' => $order,
                        'items' => $itemsToCancel,
                        'error' => $e->getMessage(),
                    ];
                    $this->whenFailedCallback && ($this->whenFailedCallback)($this->failedActionArguments);
                }
            })
            ->visible(fn() => $this->getLivewire()->getOwnerRecord()->status === OrderStatus::PENDING);
    }

    public function failedAction(\Closure $callback){
        $this->whenFailedCallback = $callback;
        return $this;
    }


}

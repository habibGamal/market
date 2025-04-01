<?php

namespace App\Filament\Actions\Forms;

use Awcodes\TableRepeater\Components\TableRepeater;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Get;
use Filament\Forms\Set;


class ReleaseDatesFormAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'releaseDatesForm';
    }

    public static function releaseDateWarning($product)
    {
        if (!$product)
            return [];

        $expirationDurationInDays = $product->expirationDurationInDays();
        return [
            'x-on:change' => '()=>{
                    const releaseDate = new Date($event.target.value);
                    const expirationDurationInDays = "' . $expirationDurationInDays . '";
                    let halfLife = new Date(releaseDate);
                    const duration = parseInt(expirationDurationInDays);
                    halfLife.setDate(halfLife.getDate() + Math.floor(duration / 2));
                    const nowDate = new Date();
                    if (nowDate > halfLife) {
                        $event.target.classList.add("!text-yellow-500");
                    }else {
                        $event.target.classList.remove("!text-yellow-500");
                    }
                }',
            'x-init' => '()=>{
                    const releaseDate = new Date($el.value);
                    const expirationDurationInDays = "' . $expirationDurationInDays . '";
                    let halfLife = new Date(releaseDate);
                    const duration = parseInt(expirationDurationInDays);
                    halfLife.setDate(halfLife.getDate() + Math.floor(duration / 2));
                    const nowDate = new Date();
                    if (nowDate > halfLife) {
                        $el.classList.add("!text-yellow-500");
                    }else {
                        $el.classList.remove("!text-yellow-500");
                    }
                }'
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->label('تواريخ انتاج')
            ->fillForm(function (array $data, Get $get, Set $set, $record) {
                $maxPiecesNumber = $get('packets_quantity') * $record->reference_state['product']['packet_to_piece'] + $get('piece_quantity');
                return [
                    'max_pieces_sum' => $maxPiecesNumber,
                    'release_dates' => $get('release_dates'),
                ];
            })
            ->form(
                [
                    TextInput::make('max_pieces_sum')
                        ->label('إجمالي عدد القطع')
                        ->disabled(),
                    TableRepeater::make('release_dates')
                        ->label('تاريخ الإنتاج')
                        ->headers([
                            Header::make('piece_quantity')->label('عدد القطع')->width('150px'),
                            Header::make('release_date')->label('تاريخ الإنتاج')->width('150px'),
                        ])
                        ->schema([
                            TextInput::make('piece_quantity')
                                ->numeric()
                                ->required(),
                            DatePicker::make('release_date')
                                ->label('الوقت')
                                ->required()
                                ->hintColor('success')
                                ->extraAlpineAttributes(
                                    fn($get, $record) => self::releaseDateWarning(\App\Models\Product::find($record->product_id))
                                )
                                ->rules([
                                    function ($get, $record) {
                                        return function ($attribute, $value, $fail) use ($get, $record) {
                                            $product = \App\Models\Product::find($record->product_id);
                                            if (!$product)
                                                return;

                                            if ($product->isExpired(\Carbon\Carbon::parse($value))) {
                                                $fail('منتج منتهي الصلاحية');
                                            }
                                        };
                                    }
                                ]),
                        ])
                        ->reorderable(false)
                ]
            )->action(function (array $data, Get $get, Set $set, $record, Action $action): void {
                $maxPiecesNumber = $get('packets_quantity') * $record->reference_state['product']['packet_to_piece'] + $get('piece_quantity');
                $sumOfPieces = array_sum(array_column($data['release_dates'], 'piece_quantity'));
                if ($sumOfPieces != $maxPiecesNumber) {
                    $action->failureNotification(
                        Notification::make()
                            ->title(
                                'عدد القطع غير متطابق'
                            )
                            ->danger()
                            ->send()
                    )->halt()->failure();
                }
                $set('release_dates', $data['release_dates']);
            });
    }
}

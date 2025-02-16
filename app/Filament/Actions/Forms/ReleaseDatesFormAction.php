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
                                ->required(),
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

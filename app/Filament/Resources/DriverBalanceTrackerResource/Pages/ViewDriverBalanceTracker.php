<?php

namespace App\Filament\Resources\DriverBalanceTrackerResource\Pages;

use App\Filament\Resources\DriverBalanceTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewDriverBalanceTracker extends ViewRecord
{
    protected static string $resource = DriverBalanceTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('معلومات العملية')
                    ->schema([
                        Components\TextEntry::make('id')
                            ->label('رقم العملية'),

                        Components\TextEntry::make('driver.name')
                            ->label('مندوب التسليم'),

                        Components\TextEntry::make('transaction_type')
                            ->label('نوع العملية')
                            ->badge(),

                        Components\TextEntry::make('operation')
                            ->label('نوع الحركة')
                            ->badge(),

                        Components\TextEntry::make('amount')
                            ->label('المبلغ')
                            ->money('EGP'),

                        Components\TextEntry::make('balance_before')
                            ->label('الرصيد قبل العملية')
                            ->money('EGP'),

                        Components\TextEntry::make('balance_after')
                            ->label('الرصيد بعد العملية')
                            ->money('EGP'),

                        Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        Components\TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),

                        Components\TextEntry::make('createdBy.name')
                            ->label('تم بواسطة'),

                        Components\TextEntry::make('created_at')
                            ->label('تاريخ العملية')
                            ->dateTime('Y-m-d h:i A'),
                    ])
                    ->columns(2),
            ]);
    }
}

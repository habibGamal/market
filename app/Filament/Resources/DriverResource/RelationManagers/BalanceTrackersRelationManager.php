<?php

namespace App\Filament\Resources\DriverResource\RelationManagers;

use App\Enums\BalanceOperation;
use App\Enums\DriverBalanceTransactionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BalanceTrackersRelationManager extends RelationManager
{
    protected static string $relationship = 'balanceTrackers';

    protected static ?string $title = 'سجل حركات الرصيد';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('الرقم')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم العملية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('نوع العملية')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('operation')
                    ->label('نوع الحركة')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance_before')
                    ->label('الرصيد قبل')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('الرصيد بعد')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ العملية')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('نوع العملية')
                    ->options(DriverBalanceTransactionType::toSelectArray()),

                Tables\Filters\SelectFilter::make('operation')
                    ->label('نوع الحركة')
                    ->options(BalanceOperation::toSelectArray()),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}

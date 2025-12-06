<?php

namespace App\Filament\Resources;

use App\Enums\BalanceOperation;
use App\Enums\DriverBalanceTransactionType;
use App\Filament\Resources\DriverBalanceTrackerResource\Pages;
use App\Models\DriverBalanceTracker;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class DriverBalanceTrackerResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = DriverBalanceTracker::class;

    protected static ?string $navigationGroup = 'إدارة مندوبين التسليم';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $label = 'حركة رصيد مندوب';

    protected static ?string $pluralLabel = 'حركات أرصدة المندوبين';

    protected static ?int $navigationSort = 3;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'delete',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('driver_id')
                            ->label('مندوب التسليم')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Select::make('transaction_type')
                            ->label('نوع العملية')
                            ->options(DriverBalanceTransactionType::toSelectArray())
                            ->default(DriverBalanceTransactionType::ADJUSTMENT)
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Select::make('operation')
                            ->label('نوع الحركة')
                            ->options(BalanceOperation::toSelectArray())
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix('جنيه')
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->disabled(fn($record) => $record !== null),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('مندوب التسليم')
                    ->sortable()
                    ->searchable(),

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
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    })
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('تم بواسطة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ العملية')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('driver_id')
                    ->label('مندوب التسليم')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('نوع العملية')
                    ->options(DriverBalanceTransactionType::toSelectArray()),

                Tables\Filters\SelectFilter::make('operation')
                    ->label('نوع الحركة')
                    ->options(BalanceOperation::toSelectArray()),

                Tables\Filters\Filter::make('created_at')
                    ->label('تاريخ العملية')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriverBalanceTrackers::route('/'),
            'create' => Pages\CreateDriverBalanceTracker::route('/create'),
            'view' => Pages\ViewDriverBalanceTracker::route('/{record}'),
        ];
    }
}

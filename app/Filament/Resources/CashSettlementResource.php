<?php

namespace App\Filament\Resources;

use App\Enums\CashSettlementStatus;
use App\Enums\CashSettlementType;
use App\Filament\Resources\CashSettlementResource\Pages;
use App\Filament\Resources\CashSettlementResource\RelationManagers;
use App\Models\CashSettlement;
use App\Models\User;
use App\Services\CashSettlementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CashSettlementResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = CashSettlement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'التسويات النقدية';
    protected static ?string $modelLabel = 'تسوية نقدية';
    protected static ?string $pluralModelLabel = 'التسويات النقدية';

    public static function form(Form $form): Form
    {
        $isEdit = $form->getRecord() !== null;

        return $form
            ->schema([
                Forms\Components\Select::make('cash_settlement_account_id')
                    ->relationship('cashSettlementAccount', 'name')
                    ->label('حساب التسوية')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        $set('alias_display', null);
                        if ($state && $get('type')) {
                            static::updateAliasDisplay($set, $state, $get('type'));
                        }
                    }),

                Forms\Components\Select::make('type')
                    ->label('النوع')
                    ->options(CashSettlementType::toSelectArray())
                    ->required()
                    ->enum(CashSettlementType::class)
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        $set('alias_display', null);
                        if ($state && $get('cash_settlement_account_id')) {
                            static::updateAliasDisplay($set, $get('cash_settlement_account_id'), $state);
                        }
                    }),

                Forms\Components\Placeholder::make('alias_display')
                    ->label('اسم العملية')
                    ->content(fn(Forms\Get $get): string => $get('alias_display') ?? '')
                    ->visible(fn(Forms\Get $get): bool => !empty($get('alias_display'))),

                Forms\Components\TextInput::make('value')
                    ->label('القيمة')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),

                Forms\Components\Select::make('officer_id')
                    ->relationship('officer', 'name')
                    ->label('المسؤول')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(auth()->id()),

                Forms\Components\DatePicker::make('should_paid_at')
                    ->label('تاريخ الاستحقاق')
                    ->required()
                    ->default(now()->addDays(7)),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),

                // Hidden fields for creation (status will be unpaid by default)
                Forms\Components\Hidden::make('status')
                    ->default(CashSettlementStatus::UNPAID->value),
            ]);
    }

    private static function updateAliasDisplay(Forms\Set $set, $accountId, $type): void
    {
        $account = \App\Models\CashSettlementAccount::find($accountId);
        if (!$account)
            return;

        $alias = match ($type) {
            CashSettlementType::IN->value => $account->inlet_name_alias ?? 'إيداع',
            CashSettlementType::OUT->value => $account->outlet_name_alias ?? 'سحب',
            default => ''
        };

        $set('alias_display', $alias);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cashSettlementAccount.name')
                    ->label('حساب التسوية')
                    ->formatStateUsing(
                        fn($record) => (
                            $record->type === CashSettlementType::IN
                            ? $record->cashSettlementAccount->inlet_name_alias :
                            $record->cashSettlementAccount->outlet_name_alias) ?? $record->cashSettlementAccount->name
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn(CashSettlementType $state): string => $state->getColor()),

                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(CashSettlementStatus $state): string => $state->getColor()),

                Tables\Columns\TextColumn::make('should_paid_at')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cash_settlement_account_id')
                    ->relationship('cashSettlementAccount', 'name')
                    ->label('حساب التسوية')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options(CashSettlementType::toSelectArray()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CashSettlementStatus::toSelectArray()),

                Tables\Filters\SelectFilter::make('officer_id')
                    ->relationship('officer', 'name')
                    ->label('المسؤول')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('should_paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('should_paid_from')
                            ->label('تاريخ الاستحقاق من'),
                        Forms\Components\DatePicker::make('should_paid_until')
                            ->label('تاريخ الاستحقاق إلى'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['should_paid_from'],
                                fn($query, $date) => $query->whereDate('should_paid_at', '>=', $date)
                            )
                            ->when(
                                $data['should_paid_until'],
                                fn($query, $date) => $query->whereDate('should_paid_at', '<=', $date)
                            );
                    }),

                Tables\Filters\TernaryFilter::make('overdue')
                    ->label('متأخر الدفع')
                    ->placeholder('الكل')
                    ->trueLabel('متأخر')
                    ->falseLabel('غير متأخر')
                    ->queries(
                        true: fn($query) => $query->where('status', CashSettlementStatus::UNPAID)
                            ->where('should_paid_at', '<', now()),
                        false: fn($query) => $query->where(function ($q) {
                            $q->where('status', CashSettlementStatus::PAID)
                                ->orWhere('should_paid_at', '>=', now());
                        }),
                    ),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_as_paid')
                    ->label('تحديد كمدفوع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الدفع')
                    ->modalDescription('هل أنت متأكد من أنك تريد تحديد هذه التسوية كمدفوعة؟ سيتم تحديث الخزينة تلقائياً.')
                    ->modalSubmitActionLabel('نعم، تأكيد الدفع')
                    ->visible(fn(CashSettlement $record): bool => $record->status === CashSettlementStatus::UNPAID)
                    ->action(function (CashSettlement $record) {
                        app(CashSettlementService::class)->markAsPaid($record);
                    }),

                Tables\Actions\Action::make('mark_as_unpaid')
                    ->label('تحديد كغير مدفوع')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء الدفع')
                    ->modalDescription('هل أنت متأكد من أنك تريد إلغاء دفع هذه التسوية؟ سيتم تحديث الخزينة تلقائياً.')
                    ->modalSubmitActionLabel('نعم، إلغاء الدفع')
                    ->visible(fn(CashSettlement $record): bool => $record->status === CashSettlementStatus::PAID)
                    ->action(function (CashSettlement $record) {
                        app(CashSettlementService::class)->markAsUnpaid($record);
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_paid')
                        ->label('تحديد كمدفوع')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد الدفع للعناصر المحددة')
                        ->modalDescription('هل أنت متأكد من أنك تريد تحديد جميع التسويات المحددة كمدفوعة؟ سيتم تحديث الخزينة تلقائياً.')
                        ->action(function ($records) {
                            $service = app(CashSettlementService::class);
                            $records->each(fn($record) => $service->markAsPaid($record));
                        }),
                    Tables\Actions\BulkAction::make('mark_as_unpaid')
                        ->label('تحديد كغير مدفوع')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء الدفع للعناصر المحددة')
                        ->modalDescription('هل أنت متأكد من أنك تريد إلغاء دفع جميع التسويات المحددة؟ سيتم تحديث الخزينة تلقائياً.')
                        ->action(function ($records) {
                            $service = app(CashSettlementService::class);
                            $records->each(fn($record) => $service->markAsUnpaid($record));
                        }),
                ]),
            ])
            ->defaultSort('should_paid_at', 'desc');
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
            'index' => Pages\ListCashSettlements::route('/'),
            'create' => Pages\CreateCashSettlement::route('/create'),
            'edit' => Pages\EditCashSettlement::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'mark_as_paid',
            'mark_as_unpaid',
        ];
    }
}

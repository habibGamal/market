<?php

namespace App\Filament\Resources;

use App\Enums\CashSettlementStatus;
use App\Enums\CashSettlementType;
use App\Filament\Resources\RevenueResource\Pages;
use App\Models\CashSettlement;
use App\Services\CashSettlementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RevenueResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = CashSettlement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationGroup = 'التسويات النقدية';
    protected static ?string $modelLabel = 'إيراد';
    protected static ?string $pluralModelLabel = 'ايرادات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('type')
                    ->default(CashSettlementType::IN->value),

                Forms\Components\TextInput::make('value')
                    ->label('القيمة')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),

                Forms\Components\DatePicker::make('should_paid_at')
                    ->label('تاريخ الاستحقاق')
                    ->required()
                    ->default(now()->addDays(7)),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('status')
                    ->default(CashSettlementStatus::UNPAID->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', CashSettlementType::IN))
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->money('EGP')
                    ->sortable(),

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
                    ->searchable()
                    ->limit(50),

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
                Tables\Actions\Action::make('mark_as_paid')
                    ->label('تحديد كمدفوع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الدفع')
                    ->modalDescription('هل أنت متأكد من أنك تريد تحديد هذا الإيراد كمدفوع؟ سيتم تحديث الخزينة تلقائياً.')
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
                    ->modalDescription('هل أنت متأكد من أنك تريد إلغاء دفع هذا الإيراد؟ سيتم تحديث الخزينة تلقائياً.')
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
                        ->modalDescription('هل أنت متأكد من أنك تريد تحديد جميع الإيرادات المحددة كمدفوعة؟ سيتم تحديث الخزينة تلقائياً.')
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
                        ->modalDescription('هل أنت متأكد من أنك تريد إلغاء دفع جميع الإيرادات المحددة؟ سيتم تحديث الخزينة تلقائياً.')
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

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenues::route('/'),
            'create' => Pages\CreateRevenue::route('/create'),
            'edit' => Pages\EditRevenue::route('/{record}/edit'),
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

<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ExpenseExporter;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\Action;

class ExpenseResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?string $modelLabel = 'مصروف';
    protected static ?string $pluralModelLabel = 'المصروفات';

    public static function form(Form $form): Form
    {
        $isEdit = $form->getRecord() !== null;

        return $form
            ->schema([
                Forms\Components\Select::make('expense_type_id')
                    ->relationship('expenseType', 'name')
                    ->label('نوع المصروف')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('value')
                    ->label('القيمة')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->disabled($isEdit),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expenseType.name')
                    ->label('نوع المصروف')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->sortable()
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('تمت الموافقة من')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('accountant.name')
                    ->label('المحاسب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expense_type_id')
                    ->relationship('expenseType', 'name')
                    ->label('نوع المصروف')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('accountant_id')
                    ->relationship('accountant', 'name')
                    ->label('المحاسب')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('approved_by')
                    ->relationship('approvedBy', 'name')
                    ->label('تمت الموافقة من')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('تاريخ الإنشاء من'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('تاريخ الإنشاء إلى'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->columnSpan(2),

                Tables\Filters\TernaryFilter::make('approved')
                    ->label('حالة الموافقة')
                    ->placeholder('الكل')
                    ->trueLabel('تمت الموافقة')
                    ->falseLabel('في انتظار الموافقة')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('approved_by'),
                        false: fn ($query) => $query->whereNull('approved_by'),
                    ),
            ])
            ->filtersFormColumns(3)
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ExpenseExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()->can('approve_expense'))
                    ->action(fn (Expense $record) => $record->approve()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('موافقة')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('approve_expense'))
                        ->action(function ($records) {
                            $records->each->approve();
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('expenseType.name')
                    ->label('نوع المصروف'),
                TextEntry::make('value')
                    ->label('القيمة')
                    ->money('EGP'),
                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
                TextEntry::make('approvedBy.name')
                    ->label('تمت الموافقة من'),
                TextEntry::make('accountant.name')
                    ->label('المحاسب'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime(),
            ])
            ->columns(3);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
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
            'approve',
        ];
    }
}

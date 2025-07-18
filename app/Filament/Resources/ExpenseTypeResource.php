<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ExpenseTypeExporter;
use App\Filament\Resources\ExpenseTypeResource\Pages;
use App\Models\ExpenseType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ExpenseTypeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ExpenseType::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?string $modelLabel = 'نوع المصروف';
    protected static ?string $pluralModelLabel = 'أنواع المصروفات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('track')
                    ->label('متابعة في التقارير')
                    ->helperText('تفعيل هذا الخيار لإدراج هذا النوع من المصروفات في تقرير المركز المالي')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('track')
                    ->label('متابعة في التقارير')
                    ->sortable()
                    ->tooltip('تفعيل/إلغاء متابعة هذا النوع في تقرير المركز المالي'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('track')
                    ->label('المتابعة في التقارير')
                    ->placeholder('الكل')
                    ->trueLabel('متتبع')
                    ->falseLabel('غير متتبع'),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ExpenseTypeExporter::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseTypes::route('/'),
            'create' => Pages\CreateExpenseType::route('/create'),
            'edit' => Pages\EditExpenseType::route('/{record}/edit'),
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
            'view_daily_report',
            'view_expenses_report',
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SupplierExporter;
use App\Filament\Imports\SupplierImporter;
use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationGroup = 'إدارة المشتريات';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'الموردين';
    protected static ?string $modelLabel = 'مورد';
    protected static ?string $pluralModelLabel = 'موردين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->length(11),
                Forms\Components\TextInput::make('company_name')
                    ->label('اسم الشركة')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balanceView.receipt_total')
                    ->label('اجمالي الفواتير')
                    ->money('EGP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('balanceView.receipt_paid')
                    ->label('المدفوع من الفواتير')
                    ->money('EGP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('balanceView.issue_total')
                    ->label('اجمالي مرتجعات المشتريات')
                    ->money('EGP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('balanceView.issue_paid')
                    ->label('المدفوع من مرتجعات المشتريات')
                    ->money('EGP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('balanceView.balance')
                    ->label('المحصلة')
                    ->money('EGP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()->exporter(SupplierExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->label('استيراد')
                    ->importer(SupplierImporter::class),
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(SupplierExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SupplierResource\RelationManagers\PurchaseInvoicesRelationManager::class,
            SupplierResource\RelationManagers\ReturnPurchaseInvoicesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}

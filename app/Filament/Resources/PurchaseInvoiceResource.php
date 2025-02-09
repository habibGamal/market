<?php

namespace App\Filament\Resources;

use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\PurchaseInvoiceResource\Pages;
use App\Models\PurchaseInvoice;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Set;
use Filament\Forms\Get;

class PurchaseInvoiceResource extends InvoiceResource
{
    protected static ?string $model = PurchaseInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'فاتورة شراء';

    protected static ?string $pluralModelLabel = 'فواتير الشراء';

    protected static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'quantity' => 'الكمية',
            'price' => 'سعر العبوة',
            'total' => 'الإجمالي',
        ];
    }

    protected static function itemKeysAliases(): array
    {
        return [
            'quantity' => 'packets_quantity',
            'price' => 'packet_cost',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...self::invoiceHeader(),
                Section::make('المنتجات')
                    ->columns(6)
                    ->schema([
                        self::productSelectSearch(),
                        Actions::make(
                            [
                                self::importProductsByBrandAction()
                            ]
                        )->columnSpan(2),
                    ]),
                TableRepeater::make('items')
                    ->label('عناصر الفاتورة')
                    ->relationship('items')
                    ->extraActions([
                        self::exportCSVAction(),
                        self::importCSVAction(),
                    ])
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('الكمية')->width('150px'),
                        Header::make('packet_cost')->label('سعر شراء العبوة')->width('150px'),
                        Header::make('total')->label('الإجمالي')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('product_id')
                            ->distinct()
                            ->hidden(),
                        Forms\Components\TextInput::make('product_name')
                            ->label('المنتج')
                            ->disabled(),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->numeric()
                            ->required()
                            ->live(debounce: 250)
                            ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                $packetCost = (float) $get('packet_cost');
                                $quantity = (float) $state;
                                $set('total', $packetCost * $quantity);
                            })
                        ,
                        Forms\Components\TextInput::make('packet_cost')
                            ->numeric()
                            ->required()
                            ->live(debounce: 250)
                            ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                $quantity = (float) $get('packets_quantity');
                                $packetCost = (float) $state;
                                $set('total', $packetCost * $quantity);
                            })
                        ,
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->disabled()
                    ])
                    ->columnSpan('full')
                    ->createItemButtonLabel('إضافة عنصر'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الفاتورة'),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة'),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPurchaseInvoices::route('/'),
            'create' => Pages\CreatePurchaseInvoice::route('/create'),
            'edit' => Pages\EditPurchaseInvoice::route('/{record}/edit'),
        ];
    }
}

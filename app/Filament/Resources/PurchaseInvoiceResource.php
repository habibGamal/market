<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PurchaseInvoiceExporter;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\PurchaseInvoiceResource\Pages;
use App\Filament\Resources\PurchaseInvoiceResource\RelationManagers\ItemsRelationManager;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;


class PurchaseInvoiceResource extends InvoiceResource
{
    protected static ?string $model = PurchaseInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'فاتورة شراء';

    protected static ?string $pluralModelLabel = 'فواتير الشراء';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'quantity' => 'الكمية',
            'price' => 'سعر العبوة',
            'total' => 'الإجمالي',
        ];
    }

    public static function itemKeysAliases(): array
    {
        return [
            'quantity' => 'packets_quantity',
            'price' => 'packet_cost',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->extraAttributes([
                'x-data' => "{
                    computeItemTotal(e) {
                        const index = e.getAttribute('id').replace('data.items.','').replace('.total','');
                        const item = \$wire.data.items[index];
                        if(!item) return 0;
                        return (item.packets_quantity * item.packet_cost).toFixed(2);
                    },
                    //computeInvoiceTotal : \$wire.data.items?.reduce ? parseFloat(\$wire.data.items.reduce((acc, item) => acc + item.total, 0).toFixed(2)) : 0,
                    computeInvoiceTotal() {
                        const items = Object.values(Object.assign({},\$wire.data.items));
                        return parseFloat(items.reduce((acc, item) => acc + (item.packets_quantity * item.packet_cost), 0).toFixed(2));
                    }
                }",
            ])
            ->schema([
                ...self::invoiceHeader(),
                Section::make('المنتجات')
                    ->columns(6)
                    ->schema([
                        self::productSelectSearch()->dehydrated(false),
                        Actions::make(
                            [
                                self::importProductsByBrandAction()
                            ]
                        )->columnSpan(2),
                    ]),
                TableRepeater::make('items')
                    ->label('عناصر الفاتورة')
                    ->relationship('items', fn($query) => $query->with('product:id,name'))
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
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\TextInput::make('product_name')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product_name : $state)
                            ->disabled(),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\TextInput::make('packet_cost')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->extraAlpineAttributes([
                                'wire:ignore' => true,
                                ':value' => 'computeItemTotal($el)',
                            ])
                    ])
                    ->dehydrated(true)
                    ->columnSpan('full')
                    ->addable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('الحالة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters(static::filters())
            ->headerActions([
                Tables\Actions\ExportAction::make()
                ->exporter(PurchaseInvoiceExporter::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Actions::make([
                printAction(\Filament\Infolists\Components\Actions\Action::make('print')),
            ])->columnSpanFull()
                ->alignEnd(),
            TextEntry::make('id')
                ->label('رقم الفاتورة'),
            TextEntry::make('total')
                ->label('المجموع'),
            TextEntry::make('status')
                ->badge()
                ->label('الحالة'),
            TextEntry::make('officer.name')
                ->label('المسؤول'),
            TextEntry::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime(),
            TextEntry::make('updated_at')
                ->label('تاريخ التحديث')
                ->dateTime(),
        ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            // ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseInvoices::route('/'),
            'create' => Pages\CreatePurchaseInvoice::route('/create'),
            'edit' => Pages\EditPurchaseInvoice::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseInvoice::route('/{record}'),
        ];
    }
}

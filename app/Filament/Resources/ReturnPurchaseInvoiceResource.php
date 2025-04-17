<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ReturnPurchaseInvoiceExporter;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\ReturnPurchaseInvoiceResource\Pages;
use App\Filament\Resources\ReturnPurchaseInvoiceResource\RelationManagers\ItemsRelationManager;
use App\Models\Product;
use App\Models\ReturnPurchaseInvoice;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use Filament\Forms\Components\DatePicker;

class ReturnPurchaseInvoiceResource extends InvoiceResource
{
    protected static ?string $model = ReturnPurchaseInvoice::class;

    protected static ?string $navigationGroup = 'إدارة المشتريات';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $modelLabel = 'فاتورة مرتجع مشتريات';

    protected static ?string $pluralModelLabel = 'فواتير مرتجع المشتريات';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'packets_quantity' => 'عدد العبوات',
            'packet_cost' => 'سعر العبوة',
            'release_date' => 'تاريخ الانتاج',
            'total' => 'الإجمالي',
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
                        const packetCost = parseFloat(item.packet_cost);
                        const packetsQuantity = parseFloat(item.packets_quantity || 0);
                        return (packetsQuantity * packetCost).toFixed(2);
                    },
                    computeInvoiceTotal() {
                        const items = Object.values(Object.assign({},\$wire.data.items));
                        return parseFloat(items.reduce((acc, item) => {
                            const packetCost = parseFloat(item.packet_cost);
                            const packetsQuantity = parseFloat(item.packets_quantity || 0);
                            return acc + packetsQuantity * packetCost;
                        }, 0)).toFixed(2);
                    }
                }",
            ])
            ->schema([
                Grid::make(4)
                    ->schema([
                        self::invoiceIdPlaceholder(),
                        self::invoiceDatePlaceholder(),
                        self::updatedAtPlaceholder(),
                        self::officerPlaceholder(),
                        self::totalPlaceholder(),
                        self::statusSelect()->disabled(fn($record) => $record == null),
                        self::notesTextarea(),
                    ]),
                Section::make('بيانات المورد')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('المورد')
                            ->options(Supplier::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                    ]),
                Section::make('المنتجات')
                    ->schema([
                        Select::make('product_id')
                            ->hiddenLabel()
                            ->searchable(['name', 'barcode'])
                            ->getSearchResultsUsing(
                                fn(string $search): array =>
                                Product::where('name', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%")
                                    ->whereHas('stockItems')
                                    ->limit(10)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->dehydrated(false)
                            ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->name)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (!$state)
                                    return;
                                $product = Product::with('stockItems')->find($state);
                                $items = [...$get('items')];
                                $newItems = $product->stockItems->map(function ($stockItem) {
                                    $availableQuantity = $stockItem->piece_quantity  - $stockItem->unavailable_quantity;
                                    if ($availableQuantity <= 0) {
                                        return null;
                                    }
                                    return [
                                        'product_id' => $stockItem->product_id,
                                        'product_name' => $stockItem->product->name,
                                        'packets_quantity' => (int) ($availableQuantity / $stockItem->product->packet_to_piece),
                                        'packet_cost' => $stockItem->product->packet_cost,
                                        'release_date' => $stockItem->release_date,
                                    ];
                                })->filter()->toArray();
                                if (empty($newItems)) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('لا يوجد مخزون')
                                        ->send();
                                    $set('product_id', null);
                                    return;
                                }
                                array_push($items, ...$newItems);
                                $set('items', $items);
                                $set('product_id', null);
                            })
                            ->columnSpan(6),
                    ]),
                TableRepeater::make('items')
                    ->label('عناصر الفاتورة')
                    ->relationship('items', fn($query) => $query->with('product:id,name'))
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('عدد العبوات')->width('150px'),
                        Header::make('packet_cost')->label('سعر العبوة')->width('150px'),
                        Header::make('release_date')->label('تاريخ الإنتاج')->width('150px'),
                        Header::make('total')->label('الإجمالي')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\TextInput::make('product_name')
                            ->label('المنتج')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product_name : $state)
                            ->disabled(),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->label('عدد العبوات')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('packet_cost')
                            ->label('تكلفة العبوة')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        DatePicker::make('release_date')
                            ->label('تاريخ الإنتاج')
                            ->disabled()
                            // ->timezone('UTC')
                            ->dehydrated(true)
                            ->required(),
                        Forms\Components\TextInput::make('total')
                            ->label('الإجمالي')
                            ->disabled()
                            ->extraAlpineAttributes([
                                'wire:ignore' => true,
                                ':value' => 'computeItemTotal($el)',
                            ])
                    ])
                    ->columnSpan('full')
                    ->dehydrated(true)
                    ->defaultItems(0)
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->cloneable(false)
                    ->columns(7)
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
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('المورد')
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
            ])
            ->filters(static::filters())
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(ReturnPurchaseInvoiceExporter::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()->action(function (ReturnPurchaseInvoice $record, $action) {
                    try{
                        $record->delete();
                    }catch (\Exception $e){
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title($e->getMessage())
                            ->send();
                    }
                }),
            ])
            ->bulkActions([]);
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
            TextEntry::make('supplier.name')
                ->label('المورد'),
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
        ])->columns(3);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnPurchaseInvoices::route('/'),
            'create' => Pages\CreateReturnPurchaseInvoice::route('/create'),
            'edit' => Pages\EditReturnPurchaseInvoice::route('/{record}/edit'),
            'view' => Pages\ViewReturnPurchaseInvoice::route('/{record}'),
        ];
    }
}

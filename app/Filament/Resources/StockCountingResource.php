<?php

namespace App\Filament\Resources;

use App\Filament\Exports\StockCountingExporter;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\StockCountingResource\Pages;
use App\Models\Product;
use App\Models\StockCounting;
use App\Models\StockItem;
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
use Filament\Forms\Components\DatePicker;

class StockCountingResource extends InvoiceResource
{
    protected static ?string $model = StockCounting::class;

    protected static ?string $navigationGroup = 'إدارة المخزن';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'جرد المخزون';

    protected static ?string $pluralModelLabel = 'جرد المخزون';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'old_packets_quantity' => 'عدد العبوات القديم',
            'old_piece_quantity' => 'عدد القطع القديم',
            'new_packets_quantity' => 'عدد العبوات الجديد',
            'new_piece_quantity' => 'عدد القطع الجديد',
            'packet_cost' => 'سعر العبوة',
            'release_date' => 'تاريخ الانتاج',
            'total_diff' => 'الفرق',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->extraAttributes([
                'x-data' => "{
                    computeItemDiff(e) {
                        const index = e.getAttribute('id').replace('data.items.','').replace('.total_diff','');
                        const item = \$wire.data.items[index];
                        if(!item) return 0;

                        const packetCost = parseFloat(item.packet_cost);
                        const oldPacketsQuantity = parseFloat(item.old_packets_quantity || 0);
                        const oldPieceQuantity = parseFloat(item.old_piece_quantity || 0);
                        const newPacketsQuantity = parseFloat(item.new_packets_quantity || 0);
                        const newPieceQuantity = parseFloat(item.new_piece_quantity || 0);
                        const packetToPiece = item.product_packet_to_piece;

                        const oldTotalInPieces = (oldPacketsQuantity * packetToPiece) + oldPieceQuantity;
                        const newTotalInPieces = (newPacketsQuantity * packetToPiece) + newPieceQuantity;
                        const diffInPieces = newTotalInPieces - oldTotalInPieces;

                        return ((diffInPieces / packetToPiece) * packetCost).toFixed(2);
                    },
                    computeInvoiceTotal() {
                        const items = Object.values(Object.assign({},\$wire.data.items));
                        return parseFloat(items.reduce((acc, item) => {
                            const packetCost = parseFloat(item.packet_cost);
                            const oldPacketsQuantity = parseFloat(item.old_packets_quantity || 0);
                            const oldPieceQuantity = parseFloat(item.old_piece_quantity || 0);
                            const newPacketsQuantity = parseFloat(item.new_packets_quantity || 0);
                            const newPieceQuantity = parseFloat(item.new_piece_quantity || 0);
                            const packetToPiece = item.product_packet_to_piece;

                            const oldTotalInPieces = (oldPacketsQuantity * packetToPiece) + oldPieceQuantity;
                            const newTotalInPieces = (newPacketsQuantity * packetToPiece) + newPieceQuantity;
                            const diffInPieces = newTotalInPieces - oldTotalInPieces;

                            return acc + ((diffInPieces / packetToPiece) * packetCost);
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
                        self::notesTextarea()->name('note'),
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
                                    $availableQuantity = $stockItem->piece_quantity;
                                    // if ($availableQuantity <= 0) {
                                    //     return null;
                                    // }
                                    $packetsQuantity = (int) ($availableQuantity / $stockItem->product->packet_to_piece);
                                    $pieceQuantity = $availableQuantity % $stockItem->product->packet_to_piece;

                                    return [
                                        'product_id' => $stockItem->product_id,
                                        'product_name' => $stockItem->product->name,
                                        'product_packet_to_piece' => $stockItem->product->packet_to_piece,
                                        'old_packets_quantity' => $packetsQuantity,
                                        'old_piece_quantity' => $pieceQuantity,
                                        'new_packets_quantity' => $packetsQuantity,
                                        'new_piece_quantity' => $pieceQuantity,
                                        'packet_cost' => $stockItem->product->packet_cost,
                                        'release_date' => $stockItem->release_date,
                                        'is_new' => false,
                                    ];
                                })->filter();

                                $existingIndexes = collect($items)
                                    ->map(fn($item) => $item['product_id'] . '_' . $item['release_date']);

                                $newItems = $newItems
                                    ->filter(fn($item) => !$existingIndexes->contains($item['product_id'] . '_' . $item['release_date']))
                                    ->values()
                                    ->toArray();

                                if (empty($newItems)) {
                                    $newItems = [
                                        [
                                            'product_id' => $state,
                                            'product_name' => $product->name,
                                            'product_packet_to_piece' => $product->packet_to_piece,
                                            'old_packets_quantity' => 0,
                                            'old_piece_quantity' => 0,
                                            'new_packets_quantity' => 0,
                                            'new_piece_quantity' => 0,
                                            'packet_cost' => $product->packet_cost,
                                            'release_date' => now(),
                                            'is_new' => true,
                                        ]
                                    ];
                                }

                                array_push($items, ...$newItems);
                                $set('items', $items);
                                $set('product_id', null);
                            })
                            ->columnSpan(6),
                    ]),
                TableRepeater::make('items')
                    ->label('عناصر الجرد')
                    ->relationship('items', fn($query) => $query->with('product:id,name,packet_to_piece'))
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('100px'),
                        Header::make('old_packets_quantity')->label('العبوات (قديم)')->width('100px'),
                        Header::make('old_piece_quantity')->label('القطع (قديم)')->width('100px'),
                        Header::make('new_packets_quantity')->label('العبوات (جديد)')->width('100px'),
                        Header::make('new_piece_quantity')->label('القطع (جديد)')->width('100px'),
                        Header::make('packet_cost')->label('سعر العبوة')->width('100px'),
                        Header::make('release_date')->label('تاريخ الإنتاج')->width('100px'),
                        Header::make('total_diff')->label('الفرق')->width('100px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('is_new'),
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\Hidden::make('product_packet_to_piece')->dehydrated(false)
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product->packet_to_piece : $state),
                        Forms\Components\TextInput::make('product_name')
                            ->label('المنتج')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product_name : $state)
                            ->disabled(),
                        Forms\Components\TextInput::make('old_packets_quantity')
                            ->label('عدد العبوات (قديم)')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(true)
                            ->minValue(0),
                        Forms\Components\TextInput::make('old_piece_quantity')
                            ->label('عدد القطع (قديم)')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(true)
                            ->minValue(0),
                        Forms\Components\TextInput::make('new_packets_quantity')
                            ->label('عدد العبوات (جديد)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('new_piece_quantity')
                            ->label('عدد القطع (جديد)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('packet_cost')
                            ->label('تكلفة العبوة')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(true)
                            ->minValue(0),
                        DatePicker::make('release_date')
                            ->label('تاريخ الإنتاج')
                            ->required()
                            ->disabled(fn($get) => !$get('is_new'))
                            ->rules([
                                fn($get) => function($attribute, $value, $fail) use ($get) {
                                    if (!$get('is_new')) return;

                                    $exists = StockItem::where('product_id', $get('product_id'))
                                        ->where('release_date', $value)
                                        ->exists();

                                    if ($exists) {
                                        $fail('تاريخ الإنتاج موجود مسبقاً لهذا المنتج');
                                    }
                                }
                            ])
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('total_diff')
                            ->label('الفرق')
                            ->disabled()
                            ->extraAlpineAttributes([
                                'wire:ignore' => true,
                                ':value' => 'computeItemDiff($el)',
                            ])
                    ])
                    ->columnSpan('full')
                    ->dehydrated(true)
                    ->defaultItems(0)
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->cloneable(false)
                    ->columns(9)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الجرد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_diff')
                    ->label('إجمالي الفرق')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('الحالة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
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
                    ->label('تصدير')
                    ->exporter(StockCountingExporter::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()->action(function (StockCounting $record, $action) {
                    try {
                        $record->delete();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title($e->getMessage())
                            ->send();
                    }
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(StockCountingExporter::class),
                ]),
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
                ->label('رقم الجرد'),
            TextEntry::make('total_diff')
                ->label('إجمالي الفرق'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockCountings::route('/'),
            'create' => Pages\CreateStockCounting::route('/create'),
            'edit' => Pages\EditStockCounting::route('/{record}/edit'),
            'view' => Pages\ViewStockCounting::route('/{record}'),
        ];
    }
}

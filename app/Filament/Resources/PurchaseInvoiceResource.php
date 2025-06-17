<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PurchaseInvoiceExporter;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\PurchaseInvoiceResource\Pages;
use App\Filament\Resources\PurchaseInvoiceResource\RelationManagers\ItemsRelationManager;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;


class PurchaseInvoiceResource extends InvoiceResource implements HasShieldPermissions
{
    protected static ?string $model = PurchaseInvoice::class;

    protected static ?string $navigationGroup = 'إدارة المشتريات';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $modelLabel = 'فاتورة شراء';

    protected static ?string $pluralModelLabel = 'فواتير الشراء';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'packets_quantity' => 'الكمية',
            'packet_cost' => 'سعر العبوة',
            'total' => 'الإجمالي',
        ];
    }


    public static function incrementQuantity($item)
    {
        $item['packets_quantity'] += 1;
        $item['total'] = $item['packets_quantity'] * $item['packet_cost'];
        return $item;
    }

    public static function addProduct($product)
    {
        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'packets_quantity' => 1,
            'packet_cost' => $product->packet_cost,
            'total' => $product->packet_cost,
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

                Section::make('بيانات الفاتورة')
                    ->schema([
                        Forms\Components\DatePicker::make('execution_date')
                            ->label('تاريخ التنفيذ')
                            ->nullable()
                            ->minDate(today()),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('تاريخ الدفع')
                            ->nullable()
                            ->minDate(today()),
                    ])
                    ->columns(2),

                Section::make('بيانات المورد')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('المورد')
                            ->options(function () {
                                return Supplier::all()->mapWithKeys(function ($supplier) {
                                    $label = $supplier->name;
                                    if ($supplier->company_name) {
                                        $label .= ' - ' . $supplier->company_name;
                                    }
                                    return [$supplier->id => $label];
                                });
                            })
                            ->searchable(['name', 'company_name'])
                            ->required()
                    ]),
                Section::make('المنتجات')
                    ->columns(6)
                    ->schema([
                        self::productSelectSearch(
                            [self::class, 'incrementQuantity'],
                            [self::class, 'addProduct']
                        )->dehydrated(false),
                        Actions::make(
                            [
                                self::importProductsByBrandAction(
                                    [self::class, 'incrementQuantity'],
                                    [self::class, 'addProduct']
                                )
                            ]
                        )->columnSpan(2),
                    ]),
                TableRepeater::make('items')
                    ->label('عناصر الفاتورة')
                    ->relationship('items', fn($query) => $query->with('product:id,name'))
                    ->extraActions([
                        // self::exportCSVAction(
                        //     fn($item, $product) => [
                        //         $product->id,
                        //         $product->name,
                        //         $item['packets_quantity'],
                        //         $item['packet_cost'],
                        //         $item['total'],
                        //     ]
                        // ),
                        // self::importCSVAction(
                        //     fn($item, $product) => [
                        //         'product_id' => $product->id,
                        //         'product_name' => $product->name,
                        //         'packets_quantity' => (float) $record[static::csvTitles()['quantity']],
                        //         'packet_cost' => (float) $record[static::csvTitles()['quantity']],
                        //         'total' => $record[static::csvTitles()['quantity']] * $record[static::csvTitles()['price']],
                        //     ]
                        // ),
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
                            ->label('المنتج')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product_name : $state)
                            ->disabled(),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\TextInput::make('packet_cost')
                            ->label('سعر العبوة')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('total')
                            ->label('الإجمالي')
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
                Tables\Columns\TextColumn::make('execution_date')
                    ->label('تاريخ التنفيذ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.company_name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('execution_date')
                    ->label('تاريخ التنفيذ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
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
                Tables\Actions\Action::make('view_receipt')
                    ->label('عرض إذن الإستلام')
                    ->icon('heroicon-o-document')
                    ->visible(fn(PurchaseInvoice $record) => $record->receipt_note_id !== null)
                    ->url(fn(PurchaseInvoice $record) => $record->receipt_note_id
                        ? ReceiptNoteResource::getUrl('view', ['record' => $record->receipt_note_id])
                        : null),
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
            TextEntry::make('receipt_note_id')
                ->label('إذن الإستلام')
                ->formatStateUsing(fn($state) => $state ? $state : 'غير متوفر')
                ->suffixAction(
                    \Filament\Infolists\Components\Actions\Action::make('viewReceiptNote')
                        ->label('عرض إذن الإستلام')
                        ->url(fn($record) => $record->receipt_note_id
                            ? ReceiptNoteResource::getUrl('view', ['record' => $record->receipt_note_id])
                            : null)
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record->receipt_note_id !== null)
                ),
            TextEntry::make('total')
                ->label('المجموع'),
            TextEntry::make('status')
                ->badge()
                ->label('الحالة'),
            TextEntry::make('execution_date')
                ->label('تاريخ التنفيذ')
                ->date(),
            TextEntry::make('payment_date')
                ->label('تاريخ الدفع')
                ->date(),
            TextEntry::make('officer.name')
                ->label('المسؤول'),
            TextEntry::make('supplier.name')
                ->label('المورد'),
            TextEntry::make('supplier.company_name')
                ->label('اسم الشركة'),
            TextEntry::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime(),
            TextEntry::make('updated_at')
                ->label('تاريخ التحديث')
                ->dateTime(),
            TextEntry::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
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


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'view_reports',
        ];
    }
}

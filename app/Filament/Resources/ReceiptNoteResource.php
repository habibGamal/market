<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Enums\PaymentStatus;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\ReceiptNoteResource\Pages;
use App\Filament\Resources\ReceiptNoteResource\RelationManagers;
use App\Filament\Traits\InvoiceActions;
use App\Filament\Traits\InvoiceLikeFilters;
use App\Filament\Traits\InvoiceLikeFormFields;
use App\Models\ReceiptNote;
use App\Filament\Actions\Forms\ReleaseDatesFormAction;
use Awcodes\TableRepeater\Components\TableRepeater;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Header;
use App\Filament\Exports\ReceiptNoteExporter;
use Filament\Forms\Set;

class ReceiptNoteResource extends Resource implements HasShieldPermissions
{
    use InvoiceLikeFormFields, InvoiceLikeFilters, InvoiceActions;

    protected static ?string $model = ReceiptNote::class;

    protected static ?string $navigationGroup = 'إدارة المخزن';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $modelLabel = 'اذن استلام';

    protected static ?string $pluralModelLabel = 'اذونات الاستلام';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'packets_quantity' => 'عدد العبوات',
            'piece_quantity' => 'عدد القطع',
            'packet_cost' => 'سعر العبوة',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        self::invoiceIdPlaceholder(),
                        self::invoiceDatePlaceholder(),
                        self::updatedAtPlaceholder(),
                        self::officerPlaceholder(),
                        self::statusSelect(),
                        self::notesTextarea(),
                        Select::make('note_type')
                            ->label('نوع الإذن')
                            ->options(ReceiptNoteType::toSelectArray())
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),
                    ]),
                TableRepeater::make('items')
                    ->label('عناصر اذن الاستلام')
                    ->relationship('items')
                    ->extraActions([
                        self::exportCSVAction(
                            fn($item, $product) => [
                                'product_id' => $product->id,
                                'product_name' => $product->name,
                                'packets_quantity' => $item['packets_quantity'],
                                'piece_quantity' => $item['piece_quantity'],
                                'packet_cost' => $item['packet_cost'],
                                // 'release_date' => $item['release_date'],
                                // 'release_dates' => $item['release_dates'],
                            ]
                        ),
                    ])
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('عدد العبوات')->width('150px'),
                        Header::make('piece_quantity')->label('عدد القطع')->width('150px'),
                        Header::make('packet_cost')->label('سعر العبوة')->width('150px'),
                        Header::make('release_date')->label('تاريخ انتاج')->width('150px'),
                        Header::make('release_dates')->label('اكثر من تاريخ انتاج')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\Hidden::make('release_dates'),
                        Forms\Components\TextInput::make('product_name')
                            ->label('المنتج')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->reference_state['product']['name'] : $state)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->label('عدد العبوات')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(
                                fn($state, $record) => $record ? $record->reference_state['packets_quantity'] : $state
                            ),
                        Forms\Components\TextInput::make('piece_quantity')
                            ->label('عدد القطع')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(
                                fn($state, $record) => $record ? $record->reference_state['piece_quantity'] ?? 0 : $state
                            ),
                        Forms\Components\TextInput::make('packet_cost')
                            ->label('سعر العبوة')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\DatePicker::make('release_dates.0.release_date')
                            ->label('تاريخ الإنتاج')
                            ->required()
                            ->hintColor('success')
                            ->extraAlpineAttributes(
                                fn($get) => ReleaseDatesFormAction::releaseDateWarning(\App\Models\Product::find($get('product_id')))
                            )
                            ->rules([
                                function ($get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $productId = $get('product_id');
                                        if (!$productId)
                                            return;

                                        $product = \App\Models\Product::find($productId);
                                        if (!$product)
                                            return;

                                        if ($product->isExpired(\Carbon\Carbon::parse($value))) {
                                            $fail('منتج منتهي الصلاحية');
                                        }
                                    };
                                }
                            ]),
                        Actions::make(
                            [
                                ReleaseDatesFormAction::make(),
                            ]
                        ),

                    ])
                    ->dehydrated(true)
                    ->columnSpan('full')
                    ->mutateRelationshipDataBeforeFillUsing(
                        function (array $data) {
                            if (auth()->user()->can('show_costs_receipt::note'))
                                return $data;
                            $hiddens = [
                                'packet_cost',
                                'reference_state',
                            ];
                            foreach ($hiddens as $hidden) {
                                unset($data[$hidden]);
                            }
                            return $data;
                        }
                    )
                    ->deletable(false)
                    ->addable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الإذن')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('الحالة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('note_type')
                    ->badge()
                    ->label('نوع الإذن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->label(label: 'حالة الدفع')
                    ->formatStateUsing(fn($record) => $record->note_type === ReceiptNoteType::PURCHASES ? $record->payment_status : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseInvoice.id')
                    ->label('رقم فاتورة المشتريات')
                    ->url(fn($record) => $record->purchaseInvoice ? PurchaseInvoiceResource::getUrl('view', ['record' => $record->purchaseInvoice]) : null)
                    ->searchable()
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
                    ->exporter(ReceiptNoteExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
                ->label('رقم الإذن'),
            TextEntry::make('purchaseInvoice.id')
                ->label('فاتورة المشتريات')
                ->visible(fn($record) => $record->purchaseInvoice !== null)
                ->formatStateUsing(fn($state) => $state ? $state : 'غير متوفر')
                ->suffixAction(
                    \Filament\Infolists\Components\Actions\Action::make('viewPurchaseInvoice')
                        ->label('عرض فاتورة المشتريات')
                        ->url(fn($record) => $record->purchaseInvoice
                            ? PurchaseInvoiceResource::getUrl('view', ['record' => $record->purchaseInvoice->id])
                            : null)
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record->purchaseInvoice !== null)
                ),
            TextEntry::make('total')
                ->label('المجموع')
                ->visible(fn() => auth()->user()->can('show_costs_receipt::note')),
            TextEntry::make('status')
                ->badge()
                ->label('الحالة'),
            TextEntry::make('note_type')
                ->badge()
                ->label('نوع الإذن'),
            TextEntry::make('payment_status')
                ->badge()
                ->label('حالة الدفع')
                ->visible(fn($record) => $record->note_type === ReceiptNoteType::PURCHASES),
            TextEntry::make('total_paid')
                ->label('إجمالي المدفوع')
                ->money('EGP')
                ->visible(fn($record) => $record->note_type === ReceiptNoteType::PURCHASES && $record->total_paid > 0),
            TextEntry::make('remaining_amount')
                ->label('المبلغ المتبقي')
                ->money('EGP')
                ->visible(fn($record) => $record->note_type === ReceiptNoteType::PURCHASES && $record->remaining_amount > 0),
            TextEntry::make('officer.name')
                ->label('المسؤول'),
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
            // RelationManagers\AccountantIssueNotesRelationManager::class,
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
            'show_costs',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceiptNotes::route('/'),
            'create' => Pages\CreateReceiptNote::route('/create'),
            'view' => Pages\ViewReceiptNote::route('/{record}'),
            'edit' => Pages\EditReceiptNote::route('/{record}/edit'),
        ];
    }
}

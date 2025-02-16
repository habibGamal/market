<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\ReceiptNoteResource\Pages;
use App\Models\ReceiptNote;
use App\Filament\Actions\Forms\ReleaseDatesFormAction;
use Awcodes\TableRepeater\Components\TableRepeater;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Header;


class ReceiptNoteResource extends InvoiceResource
{
    protected static ?string $model = ReceiptNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'اذن استلام';

    protected static ?string $pluralModelLabel = 'اذونات الاستلام';


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
            ->schema([
                Grid::make(4)
                    ->schema([
                        self::invoiceIdPlaceholder(),
                        self::invoiceDatePlaceholder(),
                        self::updatedAtPlaceholder(),
                        self::officerPlaceholder(),
                        self::statusSelect(),
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
                        self::exportCSVAction(),
                    ])
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('عدد العبوات')->width('150px'),
                        Header::make('piece_quantity')->label('عدد القطع')->width('150px'),
                        Header::make('release_date')->label('تاريخ انتاج')->width('150px'),
                        Header::make('release_dates')->label('اكثر من تاريخ انتاج')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\Hidden::make('release_dates'),
                        Forms\Components\TextInput::make('product_name')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->reference_state['product']['name'] : $state)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(
                                fn($state, $record) => $record ? $record->reference_state['packets_quantity'] : $state
                            ),
                        Forms\Components\TextInput::make('piece_quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(
                                fn($state, $record) => $record ? $record->reference_state['piece_quantity'] : $state
                            ),
                        Forms\Components\DatePicker::make('release_dates.0.release_date')
                            ->label('تاريخ الإنتاج')
                            ->required(),
                        Actions::make(
                            [
                                ReleaseDatesFormAction::make(),
                            ]
                        ),

                    ])
                    ->dehydrated(true)
                    ->columnSpan('full')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListReceiptNotes::route('/'),
            'create' => Pages\CreateReceiptNote::route('/create'),
            'view' => Pages\ViewReceiptNote::route('/{record}'),
            'edit' => Pages\EditReceiptNote::route('/{record}/edit'),
        ];
    }
}

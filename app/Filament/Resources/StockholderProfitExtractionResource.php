<?php

namespace App\Filament\Resources;

use App\Filament\Exports\StockholderProfitExtractionExporter;
use App\Filament\Resources\StockholderProfitExtractionResource\Pages;
use App\Models\Stockholder;
use App\Models\StockholderProfitExtraction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;

class StockholderProfitExtractionResource extends Resource
{
    protected static ?string $model = StockholderProfitExtraction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?string $modelLabel = 'صرف أرباح شريك';
    protected static ?string $pluralModelLabel = 'صرف أرباح الشركاء';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stockholder_id')
                    ->label('الشريك')
                    ->options(Stockholder::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->preload(),

                Forms\Components\TextInput::make('profit')
                    ->label('مبلغ الأرباح')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->suffix('جنيه'),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stockholder.name')
                    ->label('الشريك')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('profit')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),

                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('stockholder_id')
                    ->label('الشريك')
                    ->options(Stockholder::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(StockholderProfitExtractionExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Grid::make(3)->schema([
                    TextEntry::make('stockholder.name')
                        ->label('الشريك')
                        ->extraAttributes(['class' => 'font-bold']),

                    TextEntry::make('profit')
                        ->label('المبلغ')
                        ->money('EGP'),

                    TextEntry::make('notes')
                        ->label('ملاحظات')
                        ->columnSpanFull(),

                    TextEntry::make('officer.name')
                        ->label('المسؤول'),

                    TextEntry::make('created_at')
                        ->label('تاريخ الإنشاء')
                        ->dateTime(),
                ])->columnSpanFull(),
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
            'index' => Pages\ListStockholderProfitExtractions::route('/'),
            'create' => Pages\CreateStockholderProfitExtraction::route('/create'),
            'view' => Pages\ViewStockholderProfitExtraction::route('/{record}'),
        ];
    }
}

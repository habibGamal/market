<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\StockStateReportExporter;
use App\Filament\Resources\Reports\StockStateReportResource\Pages;
use App\Models\Product;
use App\Services\Reports\StockStateReportService;
use App\Filament\Widgets\StockStateStatsOverview;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockStateReportResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'إدارة المخزن';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'تقرير حالة المخزون';

    protected static ?string $pluralModelLabel = 'تقارير حالة المخزون';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_stock_state_report_product');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_stock_state_report_product');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('available_stock')
                    ->label('كمية المتاح')
                    ->formatStateUsing(function ($state, Product $record) {
                        $packets = $state / $record->packet_to_piece;
                        return "{$state} قطعة = {$packets} عبوة";
                    })
                    ->color('success')
                    ->sortable(),
                TextColumn::make('available_stock_cost')
                    ->label('تكلفة المتاح')
                    ->money('EGP')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('returned_stock')
                    ->label('كمية المرتجع من المشتريات')
                    ->formatStateUsing(function ($state, Product $record) {
                        $packets = $state / $record->packet_to_piece;
                        return "{$state} قطعة = {$packets} عبوة";
                    })
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('returned_stock_cost')
                    ->label('تكلفة المرتجع من المشتريات')
                    ->money('EGP')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('waste_stock')
                    ->label('كمية الهالك')
                    ->formatStateUsing(function ($state, Product $record) {
                        $packets = $state / $record->packet_to_piece;
                        return "{$state} قطعة = {$packets} عبوة";
                    })
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('waste_stock_cost')
                    ->label('تكلفة الهالك')
                    ->money('EGP')
                    ->color('danger')
                    ->sortable(),
            ])
            ->defaultSort('available_stock_cost', 'desc')
            ->modifyQueryUsing(function ($query) {
                return app(StockStateReportService::class)->getProductsWithStockInfo();
            })
            ->filters([
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->multiple()
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->label('الفئة')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(StockStateReportExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            StockStateStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockStateReports::route('/'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}

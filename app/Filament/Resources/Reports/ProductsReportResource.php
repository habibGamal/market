<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ProductsReportResource\Pages;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;
use App\Filament\Widgets\ProductsStatsOverview;
use App\Models\Product;
use App\Traits\ReportsFilter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ProductsReportResource extends Resource
{
    use ReportsFilter;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
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

                TextColumn::make('order_items_sum_piece_quantity')
                    ->label('كمية المبيعات')
                    ->sortable(),

                TextColumn::make('order_items_sum_total')
                    ->label('قيمة المبيعات')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('order_items_sum_profit')
                    ->label('ارباح المنتج')
                    ->money('EGP')
                    ->sortable(),

            ])
            ->filters([
                Filter::make('report_filter')->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        $subQuery = DB::table('order_items')
                            ->select(
                                'order_items.product_id',
                                DB::raw('SUM(order_items.piece_quantity) as order_items_sum_piece_quantity'),
                                DB::raw('SUM(order_items.packets_quantity) as order_items_sum_packets_quantity'),
                                DB::raw('SUM(order_items.total) as order_items_sum_total'),
                                DB::raw('SUM(order_items.profit) as order_items_sum_profit')

                            )
                            ->join('orders', 'orders.id', '=', 'order_items.order_id')
                            ->whereBetween('orders.created_at', [$data['start_date'], $data['end_date']])
                            ->groupBy('order_items.product_id');
                        $query->addSelect([
                            'products.*',
                            DB::raw('agg.order_items_sum_piece_quantity * products.packet_to_piece + agg.order_items_sum_piece_quantity as order_items_sum_piece_quantity'),
                            'order_items_sum_total' => 'agg.order_items_sum_total',
                            'order_items_sum_profit' => 'agg.order_items_sum_profit',
                        ])
                            ->leftJoinSub($subQuery, 'agg', function ($join) {
                                $join->on('products.id', '=', 'agg.product_id');
                            });
                        return $query;
                    })
            ])
            ->filtersFormColumns(3);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProductsStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductsReports::route('/'),
            'view' => Pages\ViewProductsReport::route('/{record}'),
        ];
    }
}

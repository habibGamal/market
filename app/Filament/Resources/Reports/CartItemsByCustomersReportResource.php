<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\CartItemsByCustomersReportExporter;
use App\Filament\Resources\Reports\CartItemsByCustomersReportResource\Pages;
use App\Models\Customer;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CartItemsByCustomersReportResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'إدارة المبيعات';
    protected static ?int $navigationSort = 14;

    protected static ?string $modelLabel = 'تقرير سلة المشتريات حسب العملاء';

    protected static ?string $pluralModelLabel = 'تقارير سلة المشتريات حسب العملاء';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_report_cart_items_customer', Customer::class);
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('view_report_cart_items_customer', Customer::class);
    }

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
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gov.name')
                    ->label('المحافظة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('المدينة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('area.name')
                    ->label('المنطقة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cart_items_count')
                    ->label('عدد العناصر في السلة')
                    ->counts('cartItems')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cart.total')
                    ->label('مجموع السلة')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gov_id')
                    ->label('المحافظة')
                    ->relationship('gov', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('المدينة')
                    ->relationship('city', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('area_id')
                    ->label('المنطقة')
                    ->relationship('area', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(CartItemsByCustomersReportExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(CartItemsByCustomersReportExporter::class),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات العميل')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم العميل'),
                        TextEntry::make('phone')
                            ->label('رقم الهاتف')
                            ->url(fn($record) => "tel:{$record->phone}"),
                        TextEntry::make('gov.name')
                            ->label('المحافظة'),
                        TextEntry::make('city.name')
                            ->label('المدينة'),
                        TextEntry::make('area.name')
                            ->label('المنطقة'),
                        TextEntry::make('address')
                            ->label('العنوان')
                            ->columnSpanFull(),
                    ])->columns(3)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItemsByCustomersReports::route('/'),
            'view' => Pages\ViewCartItemsByCustomersReport::route('/{record}'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [];
    }

}

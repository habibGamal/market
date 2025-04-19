<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferResource\Pages;
use App\Models\Offer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'العروض';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'عرض';
    protected static ?string $pluralModelLabel = 'العروض';

    protected static ?string $navigationGroup = 'إدارة المبيعات';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                TextInput::make('name')
                    ->label('اسم العرض')
                    ->required(),

                DateTimePicker::make('start_at')
                    ->label('تاريخ البداية')
                    ->required(),

                DateTimePicker::make('end_at')
                    ->label('تاريخ النهاية')
                    ->required(),
                Toggle::make('is_active')
                    ->label('مفعل')
                    ->default(true),

            ]),

            Tabs::make('Offer Configuration')
                ->tabs([
                    Tabs\Tab::make('الشروط الأساسية')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('instructions.conditions.in_business_type')
                                    ->label('نوع النشاط التجاري')
                                    ->options(fn() => \App\Models\BusinessType::pluck('name', 'id'))
                                    ->multiple()
                                    ->placeholder('الكل')
                                    ->searchable(),

                                Select::make('instructions.conditions.in_gov')
                                    ->label('المحافظة')
                                    ->options(fn() => \App\Models\Gov::pluck('name', 'id'))
                                    ->multiple()
                                    ->placeholder('الكل')
                                    ->searchable(),

                                Select::make('instructions.conditions.in_cities')
                                    ->label('المدينة')
                                    ->options(fn() => \App\Models\City::pluck('name', 'id'))
                                    ->multiple()
                                    ->placeholder('الكل')
                                    ->searchable(),

                                Select::make('instructions.conditions.in_areas')
                                    ->label('المنطقة')
                                    ->options(fn() => \App\Models\Area::pluck('name', 'id'))
                                    ->multiple()
                                    ->placeholder('الكل')
                                    ->searchable(),

                                TextInput::make('instructions.conditions.min_total_packets')
                                    ->label('الحد الأدنى لعدد العبوات')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                TextInput::make('instructions.conditions.min_customer_points')
                                    ->label('الحد الأدنى لنقاط العميل')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                TextInput::make('instructions.conditions.min_total_order')
                                    ->label('الحد الأدنى لقيمة الطلب')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                        ]),

                    Tabs\Tab::make('الفئات')
                        ->schema([
                            Select::make('instructions.conditions.categories.strategy')
                                ->label('استراتيجية الفئات')
                                ->options([
                                    'general' => 'عامة',
                                    'specific' => 'محددة'
                                ])
                                ->default('general')
                                ->reactive(),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('instructions.conditions.categories.general.number_of_diff_categories')
                                        ->label('عدد الفئات المختلفة')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.categories.strategy') === 'general'),

                                    TextInput::make('instructions.conditions.categories.general.min_value')
                                        ->label('الحد الأدنى للقيمة')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.categories.strategy') === 'general'),

                                    TextInput::make('instructions.conditions.categories.general.min_packets_quantity')
                                        ->label('الحد الأدنى لعدد العبوات')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.categories.strategy') === 'general'),
                                ]),

                            \Filament\Forms\Components\Repeater::make('instructions.conditions.categories.specific')
                                ->label('الفئات المحددة')
                                ->schema([
                                    Select::make('category_id')
                                        ->label('الفئة')
                                        ->options(fn() => \App\Models\Category::pluck('name', 'id'))
                                        ->required()
                                        ->searchable(),

                                    TextInput::make('min_value')
                                        ->label('الحد الأدنى للقيمة')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('min_packets_quantity')
                                        ->label('الحد الأدنى لعدد العبوات')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                ])
                                ->columns(3)
                                ->visible(fn(\Filament\Forms\Get $get) =>
                                    $get('instructions.conditions.categories.strategy') === 'specific')
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('العلامات التجارية')
                        ->schema([
                            Select::make('instructions.conditions.brands.strategy')
                                ->label('استراتيجية العلامات التجارية')
                                ->options([
                                    'general' => 'عامة',
                                    'specific' => 'محددة'
                                ])
                                ->default('general')
                                ->reactive(),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('instructions.conditions.brands.general.number_of_diff_brands')
                                        ->label('عدد العلامات التجارية المختلفة')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.brands.strategy') === 'general'),

                                    TextInput::make('instructions.conditions.brands.general.min_value')
                                        ->label('الحد الأدنى للقيمة')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.brands.strategy') === 'general'),

                                    TextInput::make('instructions.conditions.brands.general.min_packets_quantity')
                                        ->label('الحد الأدنى لعدد العبوات')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.brands.strategy') === 'general'),
                                ]),

                            \Filament\Forms\Components\Repeater::make('instructions.conditions.brands.specific')
                                ->schema([
                                    Select::make('brand_id')
                                        ->label('العلامة التجارية')
                                        ->options(fn() => \App\Models\Brand::pluck('name', 'id'))
                                        ->required()
                                        ->searchable(),

                                    TextInput::make('min_value')
                                        ->label('الحد الأدنى للقيمة')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('min_packets_quantity')
                                        ->label('الحد الأدنى لعدد العبوات')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                ])
                                ->columns(3)
                                ->visible(fn(\Filament\Forms\Get $get) =>
                                    $get('instructions.conditions.brands.strategy') === 'specific')
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('المنتجات')
                        ->schema([
                            Select::make('instructions.conditions.products.strategy')
                                ->label('استراتيجية المنتجات')
                                ->options([
                                    'general' => 'عامة',
                                    'specific' => 'محددة'
                                ])
                                ->default('general')
                                ->reactive(),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('instructions.conditions.products.general.number_of_diff_products')
                                        ->label('عدد المنتجات المختلفة')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.products.strategy') === 'general'),

                                    TextInput::make('instructions.conditions.products.general.min_value')
                                        ->label('الحد الأدنى للقيمة')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.products.strategy') === 'general'),

                                    TextInput::make('instructions.conditions.products.general.min_packets_quantity')
                                        ->label('الحد الأدنى لعدد العبوات')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->visible(fn(\Filament\Forms\Get $get) =>
                                            $get('instructions.conditions.products.strategy') === 'general'),
                                ]),

                            \Filament\Forms\Components\Repeater::make('instructions.conditions.products.specific')
                                ->schema([
                                    Select::make('product_id')
                                        ->label('المنتج')
                                        ->options(fn() => \App\Models\Product::pluck('name', 'id'))
                                        ->required()
                                        ->searchable(),

                                    TextInput::make('min_value')
                                        ->label('الحد الأدنى للقيمة')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),

                                    TextInput::make('min_packets_quantity')
                                        ->label('الحد الأدنى لعدد العبوات')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                ])
                                ->columns(3)
                                ->visible(fn(\Filament\Forms\Get $get) =>
                                    $get('instructions.conditions.products.strategy') === 'specific')
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('الخصم')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('instructions.discount.type')
                                        ->label('نوع الخصم')
                                        ->options([
                                            'percent' => 'نسبة مئوية',
                                            'fixed' => 'قيمة ثابتة'
                                        ])
                                        ->required(),

                                    TextInput::make('instructions.discount.value')
                                        ->label('قيمة الخصم')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->rules([
                                            fn(\Filament\Forms\Get $get): \Illuminate\Validation\Rules\In =>
                                            $get('instructions.discount.type') === 'percent'
                                            ? \Illuminate\Validation\Rule::in(range(0, 100))
                                            : \Illuminate\Validation\Rule::in(range(0, 1000000))
                                        ])
                                ]),
                        ]),
                ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم العرض')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('مفعل'),

                TextColumn::make('start_at')
                    ->label('تاريخ البداية')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('end_at')
                    ->label('تاريخ النهاية')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('instructions.discount.type')
                    ->label('نوع الخصم')
                    ->formatStateUsing(fn(string $state): string => $state === 'percent' ? 'نسبة مئوية' : 'قيمة ثابتة'),

                TextColumn::make('instructions.discount.value')
                    ->label('قيمة الخصم'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListOffers::route('/'),
            'create' => Pages\CreateOffer::route('/create'),
            'edit' => Pages\EditOffer::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\SectionLocation;
use App\Enums\SectionType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\Tabs;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'قسم';

    protected static ?string $pluralModelLabel = 'الأقسام';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->disabled()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('business_type_id')
                            ->relationship('businessType', 'name')
                            ->label('نوع النشاط التجاري')
                            ->required()
                            ->searchable(),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('location')
                            ->label('موقع العرض')
                            ->options(collect(SectionLocation::cases())->mapWithKeys(fn($location) => [$location->value => $location->getLabel()]))
                            ->default(SectionLocation::HOME->value)
                            ->required(),
                        Forms\Components\Select::make('section_type')
                            ->label('نوع القسم')
                            ->options(collect(SectionType::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                            ->default(SectionType::VIRTUAL->value)
                            ->required(),
                    ])->columns(2),

                Tabs::make('Content')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('المنتجات')
                            ->schema([
                                Forms\Components\CheckboxList::make('products')
                                    ->relationship('products', 'name')
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                            ]),
                        Tabs\Tab::make('العلامات التجارية')
                            ->schema([
                                Forms\Components\CheckboxList::make('brands')
                                    ->relationship('brands', 'name')
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                            ]),
                        Tabs\Tab::make('الفئات')
                            ->schema([
                                Forms\Components\CheckboxList::make('categories')
                                    ->relationship('categories', 'name')
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(3)
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}

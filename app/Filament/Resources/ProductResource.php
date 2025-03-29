<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'منتجات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product Information')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('معلومات أساسية')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->label('الصورة')
                                    ->imageEditor()
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth('480')
                                    ->imageResizeTargetHeight('480')
                                    ->directory('product-images')
                                    ->imageCropAspectRatio('1:1')
                                    ->optimize('webp')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('name')
                                    ->label('الاسم')
                                    ->required(),
                                Forms\Components\TextInput::make('barcode')
                                    ->label('الباركود')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true),
                                Forms\Components\TextInput::make('packet_to_piece')
                                    ->label('عدد القطع في العبوة')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\Select::make('packet_alter_name')
                                    ->label('الاسم البديل للعبوة')
                                    ->options([
                                        'كرتونة' => 'كرتونة',
                                        'لفة' => 'لفة',
                                    ])
                                    ->placeholder('اختر الاسم البديل للعبوة')
                                    ->helperText('الاسم الذي سيظهر بدلاً من "عبوة" في واجهة المستخدم')
                                    ->searchable()
                                    ->allowHtml(),
                                Forms\Components\Select::make('piece_alter_name')
                                    ->label('الاسم البديل للقطعة')
                                    ->options([
                                        'علبة' => 'علبة',
                                        'قطعة' => 'قطعة',
                                    ])
                                    ->placeholder('اختر الاسم البديل للقطعة')
                                    ->helperText('الاسم الذي سيظهر بدلاً من "قطعة" في واجهة المستخدم')
                                    ->searchable()
                                    ->allowHtml(),
                                Forms\Components\TextInput::make('expiration_duration')
                                    ->label('مدة الصلاحية')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\Select::make('expiration_unit')
                                    ->label('وحدة الصلاحية')
                                    ->options(\App\Enums\ExpirationUnit::values())
                                    ->required(),
                                Forms\Components\Select::make('brand_id')
                                    ->label('العلامة التجارية')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                SelectTree::make('category_id')
                                    ->label('الفئة')
                                    ->relationship('category', 'name', 'parent_id')
                                    ->parentNullValue(-1)
                                    ->enableBranchNode()
                                    ->searchable()
                                    ->required(),
                            ]),
                        Forms\Components\Tabs\Tab::make('الأسعار والتكاليف')
                            ->schema([
                                Forms\Components\TextInput::make('min_packets_stock_limit')
                                    ->label('الحد الأدنى للمخزون (عبوات)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('packet_cost')
                                    ->label('تكلفة العبوة')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('packet_price')
                                    ->label('سعر العبوة')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->rule(
                                        fn(Forms\Get $get) =>
                                        function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $packetCost = $get('packet_cost');
                                            if ($value < $packetCost) {
                                                $fail('يجب أن يكون سعر العبوة أكبر من أو يساوي تكلفة العبوة');
                                            }
                                        }
                                    ),
                                Forms\Components\TextInput::make('piece_price')
                                    ->label('سعر القطعة')
                                    ->numeric()
                                    ->required()
                                    ->rule(
                                        fn(Forms\Get $get) =>
                                        function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $cost = $get('packet_cost') / $get('packet_to_piece');
                                            if ($value < $cost) {
                                                $fail('يجب أن يكون سعر القطعة أكبر من أو يساوي تكلفة القطعة');
                                            }
                                        }
                                    ),
                                Forms\Components\TextInput::make('before_discount.packet_price')
                                    ->label('سعر العبوة قبل الخصم')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('before_discount.piece_price')
                                    ->label('سعر القطعة قبل الخصم')
                                    ->numeric()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(ProductExporter::class),
                ImportAction::make()
                    ->importer(ProductImporter::class)

            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('الباركود')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('نشط')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration')
                    ->label('مدة الصلاحية')
                    ->sortable(['expiration_duration', 'expiration_unit']),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('الفئة')
                    ->relationship('category', 'name'),
            ])
            ->actions([
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
            RelationManagers\LimitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}

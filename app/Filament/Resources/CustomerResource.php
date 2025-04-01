<?php

namespace App\Filament\Resources;

use App\Filament\Exports\CustomerExporter;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationGroup = 'إدارة الطلبيات';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'عميل';
    protected static ?string $pluralModelLabel = 'العملاء';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('البيانات الشخصية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make(name: 'phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->length(11),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('رقم الواتساب')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),

                Section::make('العنوان')
                    ->schema([
                        Forms\Components\Select::make('gov_id')
                            ->label('المحافظة')
                            ->relationship('gov', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('city_id')
                            ->label('المدينة')
                            ->relationship('city', 'name', fn (Builder $query, Get $get) =>
                                $query->where('gov_id', $get('gov_id'))
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (Get $get) => !$get('gov_id')),
                        Forms\Components\Select::make('area_id')
                            ->label('المنطقة')
                            ->relationship('area', 'name', fn (Builder $query, Get $get) =>
                                $query->where('city_id', $get('city_id'))
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get) => !$get('city_id')),
                        Forms\Components\TextInput::make('location')
                            ->label('الموقع')
                            ->required()
                            ->maxLength(255)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('openMap')
                                    ->icon('heroicon-o-map')
                                    ->tooltip('فتح في خرائط جوجل')
                                    ->url(
                                        fn (Get $get): string => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($get('location')),
                                        true
                                    )
                            ),
                        Forms\Components\TextInput::make('village')
                            ->label('القرية')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان التفصيلي')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('بيانات الحساب')
                    ->schema([
                        Forms\Components\TextInput::make('rating_points')
                            ->label('نقاط التقييم')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('blocked')
                            ->label('محظور')
                            ->default(false),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('area.name')
                    ->label('المنطقة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gov.name')
                    ->label('المحافظة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('المدينة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating_points')
                    ->label('نقاط التقييم')
                    ->sortable(),
                Tables\Columns\IconColumn::make('blocked')
                    ->label('محظور')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area')
                    ->label('المنطقة')
                    ->relationship('area', 'name'),
                Tables\Filters\TernaryFilter::make('blocked')
                    ->label('محظور'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\ExportBulkAction::make()->exporter(CustomerExporter::class),
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(CustomerExporter::class),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}

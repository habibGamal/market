<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\SectionResource\Pages\EditSection;
use App\Models\BusinessType;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\SectionLocation;
use App\Enums\SectionType;

class MenuResource extends Resource
{
    protected static ?string $model = BusinessType::class;

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $modelLabel = 'قائمة';

    protected static ?string $pluralModelLabel = 'القوائم';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('العروض المتحركة')
                    ->description('إدارة الصور المتحركة التي تظهر في الصفحة الرئيسية')
                    ->schema([
                        Forms\Components\Repeater::make('sliders')
                            ->relationship('sliders')
                            ->defaultItems(0)
                            ->addActionLabel('إضافة عرض جديد')
                            ->itemLabel(fn(array $state): ?string => $state['sort_order'] ?? null)
                            ->orderColumn('sort_order')
                            ->collapsible()
                            ->reorderable()
                            ->cloneable()
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('الصورة')
                                    ->required()
                                    ->image(),
                                Forms\Components\TextInput::make('link')
                                    ->label('الرابط')
                                    ->url(),
                                Forms\Components\Toggle::make('active')
                                    ->label('نشط')
                                    ->default(true),
                            ])
                            ->columns(1)
                            ->grid(1),
                    ])->collapsed(),

                Forms\Components\Section::make('أقسام الصفحة الرئيسية')
                    ->description('إدارة الأقسام التي تظهر في الصفحة الرئيسية')
                    ->schema([
                        Forms\Components\Repeater::make('home_sections')
                            ->relationship('sections', fn($query) => $query->where('location', SectionLocation::HOME))
                            ->defaultItems(0)
                            ->addActionLabel('إضافة قسم جديد')
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->orderColumn('sort_order')
                            ->collapsible()
                            ->cloneable()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('العنوان')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('active')
                                    ->label('نشط')
                                    ->default(true),
                                Forms\Components\Hidden::make('section_type')
                                    ->default(SectionType::REAL->value),
                                Forms\Components\Hidden::make('location')
                                    ->default(SectionLocation::HOME->value)
                            ])
                            ->extraItemActions([
                                Forms\Components\Actions\Action::make('manage_section')
                                    ->label('إدارة القسم')
                                    ->url(fn(array $arguments, Repeater $component) => SectionResource::getUrl('edit', ['record' => $component->getRawItemState($arguments['item'])['id']]))
                                    ->icon('heroicon-o-pencil-square')
                                    ->visible(
                                        fn(array $arguments, Repeater $component) =>
                                        array_key_exists('id', $component->getRawItemState($arguments['item']))
                                        && $component->getRawItemState($arguments['item'])['section_type'] === SectionType::REAL->value
                                    ),
                            ])
                            ->deleteAction(
                                fn($action) => $action
                                    ->hidden(function (array $arguments, $component) {
                                        $items = $component->getState();
                                        $activeItem = $items[$arguments['item']];
                                        return $activeItem['section_type'] === SectionType::VIRTUAL->value;
                                    })
                            )
                            ->cloneAction(
                                fn($action) => $action
                                    ->hidden(function (array $arguments, $component) {
                                        $items = $component->getState();
                                        $activeItem = $items[$arguments['item']];
                                        return $activeItem['section_type'] === SectionType::VIRTUAL->value;
                                    })
                            )

                            ->defaultItems(0)
                            ->columns(2)
                            ->grid(1),
                    ])->collapsed(),

                Forms\Components\Section::make('أقسام العروض المميزة')
                    ->description('إدارة الأقسام التي تظهر في صفحة العروض المميزة')
                    ->schema([
                        Forms\Components\Repeater::make('hot_deals_sections')
                            ->relationship('sections', fn($query) => $query->where('location', SectionLocation::HOT_DEALS))
                            ->defaultItems(0)
                            ->addActionLabel('إضافة قسم جديد')
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->orderColumn('sort_order')
                            ->collapsible()
                            ->cloneable()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('العنوان')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('active')
                                    ->label('نشط')
                                    ->default(true),
                                Forms\Components\Hidden::make('section_type')
                                    ->default(SectionType::REAL->value),
                                Forms\Components\Hidden::make('location')
                                    ->default(SectionLocation::HOT_DEALS->value)
                            ])
                            ->extraItemActions([
                                Forms\Components\Actions\Action::make('manage_section')
                                    ->label('إدارة القسم')
                                    ->url(fn(array $arguments, Repeater $component) => SectionResource::getUrl('edit', ['record' => $component->getRawItemState($arguments['item'])['id']]))
                                    ->icon('heroicon-o-pencil-square')
                                    ->visible(
                                        fn(array $arguments, Repeater $component) =>
                                        array_key_exists('id', $component->getRawItemState($arguments['item']))
                                        && $component->getRawItemState($arguments['item'])['section_type'] === SectionType::REAL->value
                                    ),
                            ])
                            ->defaultItems(0)
                            ->columns(2)
                            ->grid(1),
                    ])->collapsed(),

                Forms\Components\Section::make('الإعلانات')
                    ->description('إدارة الإعلانات التي تظهر في الصفحة الرئيسية')
                    ->schema([
                        Forms\Components\Repeater::make('announcements')
                            ->relationship('announcements')
                            ->defaultItems(0)
                            ->addActionLabel('إضافة إعلان جديد')
                            ->schema([
                                Forms\Components\RichEditor::make('text')
                                    ->label('نص الإعلان')
                                    ->required(),
                                Forms\Components\ColorPicker::make('color')
                                    ->label('لون الإعلان')
                                    ->default('#000000'),
                                Forms\Components\Toggle::make('active')
                                    ->label('نشط')
                                    ->default(true)
                            ])
                            ->columns(1)
                    ])->collapsed()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نوع النشاط التجاري')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sections_count')
                    ->label('عدد الأقسام')
                    ->counts('sections')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('إدارة الأقسام'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}

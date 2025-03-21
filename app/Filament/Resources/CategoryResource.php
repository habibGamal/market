<?php

namespace App\Filament\Resources;

use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Filament\Resources\CategoryResource\Widgets\CategoryWidget;
use App\Models\Category;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $label = 'الفئة';
    protected static ?string $pluralLabel = 'الفئات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('الاسم')
                    ->unique(ignoreRecord: true)
                    ->required(),
                SelectTree::make('parent_id')
                    ->label('الفئة الرئيسية')
                    ->relationship('parent', 'name', 'parent_id')
                    ->disabledOptions(fn($record) => [$record?->id])
                    ->parentNullValue(-1)
                    ->enableBranchNode()
                    ->searchable(),
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('480')
                    ->imageResizeTargetHeight('480')
                    ->directory('categories-images')
                    ->required()
                    ->imageEditor()
                    ->optimize('webp')
                    ->imageCropAspectRatio('1:1'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(CategoryExporter::class),
                ImportAction::make()
                    ->importer(CategoryImporter::class),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BrandExporter;
use App\Filament\Imports\BrandImporter;
use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $label = 'العلامة التجارية';
    protected static ?string $pluralLabel = 'العلامات التجارية';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('الاسم')
                    ->unique(ignoreRecord: true)
                    ->required(),
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('480')
                    ->imageResizeTargetHeight('480')
                    ->directory('brand-images')
                    ->required()
                    ->imageEditor()
                    ->optimize('webp')
                    ->imageCropAspectRatio('1:1'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(BrandExporter::class),
                ImportAction::make()
                    ->importer(BrandImporter::class)
            ])
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Exports\AreaExporter;
use App\Filament\Imports\AreaImporter;
use App\Filament\Resources\AreaResource\Pages;
use App\Filament\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationGroup = 'إدارة النظام';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $modelLabel = 'منطقة';

    protected static ?string $pluralModelLabel = 'المناطق';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('city_id')
                    ->label('المدينة')
                    ->relationship('city', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('gov_id')
                            ->label('المحافظة')
                            ->relationship('gov', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Toggle::make('has_village')
                    ->label('لديها قرى')
                    ->default(false),
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
                TextColumn::make('city.gov.name')
                    ->label('المحافظة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city.name')
                    ->label('المدينة')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('has_village')
                    ->label('لديها قرى')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->label('المدينة')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('has_village')
                    ->label('لديها قرى'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make('export')->label('تصدير')->exporter(
                    AreaExporter::class
                ),
                ImportAction::make('import')->label('استيراد')->importer(
                    AreaImporter::class
                ),
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}

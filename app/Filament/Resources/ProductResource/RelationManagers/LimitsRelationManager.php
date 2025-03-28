<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LimitsRelationManager extends RelationManager
{
    protected static string $relationship = 'limits';

    protected static ?string $title = 'الحدود';

    protected static ?string $modelLabel = 'حد';

    protected static ?string $pluralModelLabel = 'الحدود';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('selected_areas')
                    ->label('المنطقة')
                    ->relationship('area', 'name')
                    ->preload()
                    ->multiple()
                    ->dehydrated(true)
                    ->placeholder('الكل'),
                Grid::make(4)->schema([
                    Forms\Components\TextInput::make('min_packets')
                        ->label('الحد الأدنى كرتونة')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('max_packets')
                        ->label('الحد الأقصى كرتونة')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('min_pieces')
                        ->label('الحد الأدنى للقطع')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('max_pieces')
                        ->label('الحد الأقصى للقطع')
                        ->numeric()
                        ->required(),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('area_id')
            ->columns([
                Tables\Columns\TextColumn::make('area.name')->label('المنطقة'),
                Tables\Columns\TextColumn::make('min_packets')->label('الحد الأدنى للعلب'),
                Tables\Columns\TextColumn::make('max_packets')->label('الحد الأقصى للعلب'),
                Tables\Columns\TextColumn::make('min_pieces')->label('الحد الأدنى للقطع'),
                Tables\Columns\TextColumn::make('max_pieces')->label('الحد الأقصى للقطع'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->using(function (array $data, string $model, $record): Model {
                    $limits = [];
                    $areas = empty($data['selected_areas']) ? Area::select('id')->get()->pluck('id')->toArray() : $data['selected_areas'];
                    foreach ($areas as $area) {
                        $limits[] = [
                            'area_id' => $area,
                            'min_packets' => $data['min_packets'],
                            'max_packets' => $data['max_packets'],
                            'min_pieces' => $data['min_pieces'],
                            'max_pieces' => $data['max_pieces'],
                        ];
                    }

                    return $record->createMany($limits);

                }),

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
}

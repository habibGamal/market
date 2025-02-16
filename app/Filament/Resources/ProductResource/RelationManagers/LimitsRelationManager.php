<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                Forms\Components\Select::make('area_id')
                    ->label('المنطقة')
                    ->relationship('area', 'name')
                    ->required(),
                Grid::make(4)->schema([
                    Forms\Components\TextInput::make('min_packets')
                        ->label('الحد الأدنى للعلب')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('max_packets')
                        ->label('الحد الأقصى للعلب')
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
                Tables\Actions\CreateAction::make(),
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

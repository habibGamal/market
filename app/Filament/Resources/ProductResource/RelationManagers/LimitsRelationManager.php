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
                Forms\Components\Select::make('area_id')
                    ->label('المنطقة')
                    ->relationship('area', 'name')
                    ->rules([
                        function (Forms\Get $get, string $operation, ?Model $record, $state) {
                            return ['unique:product_limits,area_id,' . ($state) . ',id,product_id,' . $this->ownerRecord->id];
                        }
                    ])
                    ->preload()
                    ->required(),
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
                Tables\Columns\TextColumn::make('min_packets')->label('الحد الأدنى كرتونة'),
                Tables\Columns\TextColumn::make('max_packets')->label('الحد الأقصى كرتونة'),
                Tables\Columns\TextColumn::make('min_pieces')->label('الحد الأدنى للقطع'),
                Tables\Columns\TextColumn::make('max_pieces')->label('الحد الأقصى للقطع'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_many')
                    ->label('إضافة حدود جديدة')
                    ->form(function (RelationManager $livewire): array {
                        $product = $livewire->ownerRecord;
                        return [
                            Forms\Components\Select::make('areas')
                                ->label('المنطقة')
                                ->options(Area::whereNotIn('id', $product->limits->pluck('area_id'))
                                    ->get()->pluck('name', 'id'))
                                ->helperText('اترك الحقل فارغاً لاختيار جميع المناطق')
                                ->multiple(),
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
                        ];
                    })
                    ->action(function (RelationManager $livewire, array $data): void {
                        $product = $livewire->ownerRecord;
                        if (count($data['areas']) > 0) {
                            // Create limits for selected areas
                            $product->limits()->createMany(
                                collect($data['areas'])->map(function ($areaId) use ($data) {
                                return [
                                    'area_id' => $areaId,
                                    'min_packets' => $data['min_packets'],
                                    'max_packets' => $data['max_packets'],
                                    'min_pieces' => $data['min_pieces'],
                                    'max_pieces' => $data['max_pieces'],
                                ];
                            })->toArray()
                            );
                        } else {
                            // Create limits for all areas
                            $product->limits()->createMany(
                                Area::whereNotIn('id', $product->limits->pluck('area_id'))
                                    ->get()
                                    ->map(function ($area) use ($data) {
                                return [
                                    'area_id' => $area->id,
                                    'min_packets' => $data['min_packets'],
                                    'max_packets' => $data['max_packets'],
                                    'min_pieces' => $data['min_pieces'],
                                    'max_pieces' => $data['max_pieces'],
                                ];
                            })->toArray()
                            );
                        }
                    })

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

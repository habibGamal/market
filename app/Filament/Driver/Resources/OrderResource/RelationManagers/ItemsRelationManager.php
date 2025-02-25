<?php

namespace App\Filament\Driver\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use App\Services\OrderServices;
use App\Services\DriverServices;
use Illuminate\Support\Collection;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'الأصناف';
    protected static ?string $modelLabel = 'صنف';
    protected static ?string $pluralModelLabel = 'الأصناف';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make([
                    'default' => 2,
                    'sm' => 2,
                    'lg' => 4,
                ])->schema([
                            Tables\Columns\TextColumn::make('product.name')
                                ->label('المنتج')
                                ->formatSateUsingLabelPrefix(),
                            Tables\Columns\TextColumn::make('total')
                                ->label('الإجمالي')
                                ->money('EGP')
                                ->formatSateUsingLabelPrefix()
                                ->color('green'),
                            Stack::make([
                                Tables\Columns\TextColumn::make('packets_quantity')
                                    ->label('عدد العبوات')
                                    ->formatSateUsingLabelPrefix(),
                                Tables\Columns\TextColumn::make('packet_price')
                                    ->label(label: 'سعر العبوة')
                                    ->money('EGP')
                                    ->formatSateUsingLabelPrefix()
                                    ->color('gray'),
                            ]),
                            Stack::make([
                                Tables\Columns\TextColumn::make('piece_quantity')
                                    ->label('عدد القطع')
                                    ->formatSateUsingLabelPrefix(),
                                Tables\Columns\TextColumn::make('piece_price')
                                    ->label('سعر القطعة')
                                    ->money('EGP')
                                    ->formatSateUsingLabelPrefix()
                                    ->color('gray'),
                            ]),
                        ])
            ]);
    }
}

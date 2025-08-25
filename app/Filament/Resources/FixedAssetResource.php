<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixedAssetResource\Pages;
use App\Models\FixedAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;

class FixedAssetResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FixedAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?string $modelLabel = 'أصل ثابت';
    protected static ?string $pluralModelLabel = 'الأصول الثابتة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الأصل')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('value')
                    ->label('القيمة')
                    ->required()
                    ->numeric()
                    ->minValue(0),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الأصل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->sortable()
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable()
                    ->limit(150),

                Tables\Columns\TextColumn::make('accountant.name')
                    ->label('المحاسب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('accountant_id')
                    ->relationship('accountant', 'name')
                    ->label('المحاسب')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('تاريخ الإنشاء من'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('تاريخ الإنشاء إلى'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->columnSpan(2),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')
                    ->label('اسم الأصل'),
                TextEntry::make('value')
                    ->label('القيمة')
                    ->money('EGP'),
                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
                TextEntry::make('accountant.name')
                    ->label('المحاسب'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime(),
            ])
            ->columns(3);
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFixedAssets::route('/'),
            'create' => Pages\CreateFixedAsset::route('/create'),
            'view' => Pages\ViewFixedAsset::route('/{record}'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'delete',
            'delete_any',
        ];
    }
}

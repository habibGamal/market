<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashSettlementAccountResource\Pages;
use App\Filament\Resources\CashSettlementAccountResource\RelationManagers;
use App\Models\CashSettlementAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CashSettlementAccountResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = CashSettlementAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'التسويات النقدية';
    protected static ?string $modelLabel = 'حساب تسوية نقدية';
    protected static ?string $pluralModelLabel = 'حسابات التسويات النقدية';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الحساب')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('inlet_name_alias')
                    ->label('اسم الإيداع المستعار')
                    ->maxLength(255),

                Forms\Components\TextInput::make('outlet_name_alias')
                    ->label('اسم السحب المستعار')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الحساب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('inlet_name_alias')
                    ->label('اسم الإيداع المستعار')
                    ->searchable(),

                Tables\Columns\TextColumn::make('outlet_name_alias')
                    ->label('اسم السحب المستعار')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cashSettlements_count')
                    ->label('عدد التسويات')
                    ->counts('cashSettlements')
                    ->sortable(),

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashSettlementAccounts::route('/'),
            'create' => Pages\CreateCashSettlementAccount::route('/create'),
            'edit' => Pages\EditCashSettlementAccount::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}

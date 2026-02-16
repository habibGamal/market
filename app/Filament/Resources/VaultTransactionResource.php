<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VaultTransactionResource\Pages;
use App\Models\Vault;
use App\Models\VaultTransaction;
use App\Services\VaultService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VaultTransactionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = VaultTransaction::class;

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $label = 'حركة الخزينة';
    protected static ?string $pluralLabel = 'حركات الخزائن';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('from_vault_id')
                    ->label('من خزينة')
                    ->options(Vault::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->distinct()
                    ->disableOptionWhen(fn ($value, $get) => $value == $get('to_vault_id'))
                    ->live(),
                Select::make('to_vault_id')
                    ->label('إلى خزينة')
                    ->options(Vault::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->distinct()
                    ->disableOptionWhen(fn ($value, $get) => $value == $get('from_vault_id'))
                    ->live(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('ج.م')
                    ->rules([
                        fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $fromVaultId = $get('from_vault_id');
                            if ($fromVaultId) {
                                $vault = Vault::find($fromVaultId);
                                if ($vault && $vault->balance < $value) {
                                    $fail('الرصيد المتاح في الخزينة غير كافٍ للتحويل');
                                }
                            }
                        },
                    ]),
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fromVault.name')
                    ->label('من خزينة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toVault.name')
                    ->label('إلى خزينة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable()
                    ->suffix(' ج.م'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ العملية')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVaultTransactions::route('/'),
            'create' => Pages\CreateVaultTransaction::route('/create'),
            'view' => Pages\ViewVaultTransaction::route('/{record}'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}

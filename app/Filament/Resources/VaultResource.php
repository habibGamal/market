<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VaultResource\Pages;
use App\Models\Vault;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VaultResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Vault::class;

    protected static ?string $navigationGroup = 'الإدارة المالية';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $label = 'الخزينة';
    protected static ?string $pluralLabel = 'الخزائن';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('balance')
                    ->label('الرصيد')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('ج.م'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('الرصيد')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable()
                    ->suffix(' ج.م'),
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
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Vault $record) => $record->id === 1 || $record->balance > 0)
                    ->before(function (Vault $record) {
                        if ($record->id === 1) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف الخزينة النقدية الافتراضية')
                                ->send();

                            return false;
                        }

                        if ($record->balance > 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('لا يمكن حذف خزينة تحتوي على رصيد')
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records, $action) {
                            $cannotDelete = $records->filter(fn ($record) => $record->id === 1 || $record->balance > 0);

                            if ($cannotDelete->isNotEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('لا يمكن حذف الخزينة النقدية الافتراضية أو الخزائن التي تحتوي على رصيد')
                                    ->send();

                                return;
                            }

                            $records->each->delete();

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('تم حذف الخزائن بنجاح')
                                ->send();
                        }),
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
            'index' => Pages\ListVaults::route('/'),
            'create' => Pages\CreateVault::route('/create'),
            'edit' => Pages\EditVault::route('/{record}/edit'),
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


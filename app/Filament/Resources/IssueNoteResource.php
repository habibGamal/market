<?php

namespace App\Filament\Resources;

use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\IssueNoteResource\Pages;
use App\Filament\Resources\IssueNoteResource\RelationManagers;
use App\Models\IssueNote;
use Awcodes\TableRepeater\Components\TableRepeater;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\TableRepeater\Header;

class IssueNoteResource extends InvoiceResource
{
    protected static ?string $model = IssueNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'اذن صرف';

    protected static ?string $pluralModelLabel = 'اذونات الصرف';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'quantity' => 'الكمية',
            'price' => 'سعر العبوة',
            'total' => 'الإجمالي',
        ];
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...self::invoiceHeader(showTotal: false),
                TableRepeater::make('items')
                    ->label('عناصر الفاتورة')
                    ->relationship('items', fn($query) => $query->with('product:id,name'))
                    ->extraActions([
                        // self::exportCSVAction(),
                        // self::importCSVAction(),
                    ])
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('عدد العبوات')->width('150px'),
                        Header::make('piece_quantity')->label('عدد القطع')->width('150px'),
                        Header::make('release_date')->label('تاريخ الانتاج')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\TextInput::make('product_name')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product_name : $state),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->numeric(),
                        Forms\Components\TextInput::make('piece_quantity')
                            ->numeric(),
                        Forms\Components\TextInput::make('release_date'),
                    ])
                    ->disabled()
                    ->mutateRelationshipDataBeforeCreateUsing(fn(array $data) => static::mutateItemsBeforeSaving($data))
                    ->mutateRelationshipDataBeforeSaveUsing(fn(array $data) => static::mutateItemsBeforeSaving($data))
                    ->columnSpan('full')
                    ->addable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListIssueNotes::route('/'),
            'create' => Pages\CreateIssueNote::route('/create'),
            'view' => Pages\ViewIssueNote::route('/{record}'),
            'edit' => Pages\EditIssueNote::route('/{record}/edit'),
        ];
    }
}

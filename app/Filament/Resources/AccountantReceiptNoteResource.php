<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountantReceiptNoteResource\Pages;
use App\Models\AccountantReceiptNote;
use App\Models\Driver;
use App\Models\IssueNote;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;

class AccountantReceiptNoteResource extends Resource
{
    protected static ?string $model = AccountantReceiptNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?string $modelLabel = 'اذن استلام نقدية';
    protected static ?string $pluralModelLabel = 'اذون استلام نقدية';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    MorphToSelect::make('fromModel')
                        ->label('نوع المستند')
                        ->types([
                            MorphToSelect\Type::make(Driver::class)
                                ->label('سائق')
                                ->modifyOptionsQueryUsing(function ($query) {
                                    $query->driversOnly();
                                })
                                ->titleAttribute('name'),
                            MorphToSelect\Type::make(IssueNote::class)
                                ->label('اذن صرف مرتجع مشتريات')
                                ->modifyOptionsQueryUsing(function ($query) {
                                    $query->needAccountantReceiptNote();
                                })
                                ->titleAttribute('id'),
                        ])
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set,Forms\Get $get, $old) {
                            // no changes in type or id
                            if ($state['from_model_type'] === $old['from_model_type'] && $state['from_model_id'] === $old['from_model_id']) {
                                return;
                            }

                            // user change the type
                            if($state['from_model_type'] !== $old['from_model_type']){
                                $set('paid', null);
                                return;
                            }
                            if (!$state['from_model_id'] || !$state['from_model_type']) {
                                return;
                            }
                            // dump($state);
                            $model = $state['from_model_type']::find($state['from_model_id']);
                            if ($state['from_model_type'] === IssueNote::class) {
                                $set('paid', $model->total);
                            }
                            if ($state['from_model_type'] === Driver::class) {
                                $set('paid', $model->account->balance);
                            }
                        })
                        ->disabled(fn($record) => $record !== null)
                        ->required(),

                    Forms\Components\TextInput::make('paid')
                        ->label('المبلغ المحصل')
                        ->numeric()
                        ->required()
                        ->disabled(fn($record) => $record !== null),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
                        ->columnSpanFull(),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_model_type')
                    ->label('نوع المستند')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        Driver::class => 'سائق',
                        IssueNote::class => 'اذن صرف',
                        default => $state
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('from_model_id')
                    ->label('رقم المستند')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid')
                    ->label('المبلغ المحصل')
                    ->sortable()
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),

                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
                \Filament\Infolists\Components\Grid::make(3)->schema([
                    // TextEntry::make('fromModel')
                    //     ->label('المستند')
                    //     ->formatStateUsing(fn($state) => match ($state::class) {
                    //         Driver::class => 'سائق: ' . $state->name,
                    //         IssueNote::class => 'اذن صرف #' . $state->id,
                    //         default => $state::class . ' #' . $state->id
                    //     })
                    //     ->extraAttributes(['class' => 'font-bold'])
                    //     ->suffixAction(
                    //         \Filament\Infolists\Components\Actions\Action::make('viewDocument')
                    //             ->label('عرض المستند')
                    //             ->url(fn(Model $record) => match ($record->from_model_type) {
                    //                 Driver::class => DriverResource::getUrl('view', ['record' => $record->from_model_id]),
                    //                 IssueNote::class => IssueNoteResource::getUrl('view', ['record' => $record->from_model_id]),
                    //                 default => '#'
                    //             })
                    //             ->icon('heroicon-m-arrow-top-right-on-square')
                    //             ->openUrlInNewTab()
                    //     ),

                    TextEntry::make('paid')
                        ->label('المبلغ المحصل')
                        ->money('EGP'),

                    TextEntry::make('notes')
                        ->label('ملاحظات')
                        ->columnSpanFull(),

                    TextEntry::make('officer.name')
                        ->label('المسؤول'),

                    TextEntry::make('created_at')
                        ->label('تاريخ الإنشاء')
                        ->dateTime(),
                ])->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountantReceiptNotes::route('/'),
            'create' => Pages\CreateAccountantReceiptNote::route('/create'),
            'view' => Pages\ViewAccountantReceiptNote::route('/{record}'),
            'edit' => Pages\EditAccountantReceiptNote::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Exports\AccountantIssueNoteExporter;
use App\Filament\Resources\AccountantIssueNoteResource\Pages;
use App\Models\AccountantIssueNote;
use App\Models\ReceiptNote;
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
use Filament\Infolists\Components\ViewEntry;

class AccountantIssueNoteResource extends Resource
{
    protected static ?string $model = AccountantIssueNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?string $modelLabel = 'اذن صرف نقدية';
    protected static ?string $pluralModelLabel = 'اذون صرف نقدية';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    MorphToSelect::make('forModel')
                        ->label('نوع المستند')
                        ->types([
                            MorphToSelect\Type::make(ReceiptNote::class)
                                ->label('اذن استلام مشتريات')
                                ->modifyOptionsQueryUsing(function ($query) {
                                    $query->needAccountantIsssueNote();
                                })
                                ->titleAttribute('id'),
                        ])
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if (!$get('for_model_id') || !$get('for_model_type')) {
                                return;
                            }
                            $model = $get('for_model_type')::find($get('for_model_id'));
                            if ($model) {
                                $set('paid', $model->total);
                            }
                        })
                        ->disabled(fn($record) => $record !== null),
                    Forms\Components\TextInput::make('paid')
                        ->label('المدفوع')
                        ->numeric()
                        ->required()
                        ->disabled(),

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
                Tables\Columns\TextColumn::make('for_model_type')
                    ->label('نوع المستند')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        ReceiptNote::class => 'اذن استلام مشتريات',
                        default => $state
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('for_model_id')
                    ->label('رقم المستند')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid')
                    ->label('المدفوع')
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
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(AccountantIssueNoteExporter::class),
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
                    TextEntry::make('forModel')
                        ->label('نوع المستند')
                        ->formatStateUsing(fn($state) => match ($state::class) {
                            ReceiptNote::class => 'اذن استلام مشتريات #' . $state->id,
                            default => $state::class . ' #' . $state->id
                        })
                        ->extraAttributes(['class' => 'font-bold'])
                        ->suffixAction(
                            \Filament\Infolists\Components\Actions\Action::make('viewDocument')
                                ->label('عرض المستند')
                                ->url(fn (Model $record) => match ($record->for_model_type) {
                                    ReceiptNote::class => ReceiptNoteResource::getUrl('view', ['record' => $record->for_model_id]),
                                    default => '#'
                                })
                                ->icon('heroicon-m-arrow-top-right-on-square')
                                ->openUrlInNewTab()
                        ),

                    TextEntry::make('paid')
                        ->label('المدفوع')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountantIssueNotes::route('/'),
            'create' => Pages\CreateAccountantIssueNote::route('/create'),
            'view' => Pages\ViewAccountantIssueNote::route('/{record}'),
            'edit' => Pages\EditAccountantIssueNote::route('/{record}/edit'),
        ];
    }
}

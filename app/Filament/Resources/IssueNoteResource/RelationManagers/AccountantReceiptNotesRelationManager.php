<?php

namespace App\Filament\Resources\IssueNoteResource\RelationManagers;

use App\Models\AccountantReceiptNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountantReceiptNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'accountantReceiptNotes';

    protected static ?string $title = 'اذون القبض النقدية';

    protected static ?string $modelLabel = 'اذن قبض نقدية';

    protected static ?string $pluralModelLabel = 'اذون قبض نقدية';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('from_model_id')
                    ->default(fn() => $this->ownerRecord->id),
                Forms\Components\Hidden::make('from_model_type')
                    ->default(fn() => get_class($this->ownerRecord)),
                Forms\Components\TextInput::make('paid')
                    ->label('المبلغ المقبوض')
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->suffix('جنيه')
                    ->required()
                    ->helperText(fn() => 'المبلغ المتبقي: ' . number_format($this->ownerRecord->remaining_amount, 2) . ' جنيه'),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الاذن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
                    ->label('المبلغ المقبوض')
                    ->money('EGP')
                    ->sortable(),
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
                Tables\Actions\CreateAction::make()
                    ->label('اذن قبض جديد')
                    ->icon('heroicon-o-plus')
                    ->visible(fn() => $this->ownerRecord->remaining_amount > 0),
                Tables\Actions\Action::make('receive_left')
                    ->label('قبض المتبقي')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn() => $this->ownerRecord->remaining_amount > 0)
                    ->action(function () {
                        AccountantReceiptNote::create([
                            'from_model_id' => $this->ownerRecord->id,
                            'from_model_type' => get_class($this->ownerRecord),
                            'paid' => $this->ownerRecord->remaining_amount,
                            'notes' => 'قبض المتبقي من إذن الصرف',
                        ]);
                        $this->ownerRecord->refresh();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد قبض المتبقي')
                    ->modalDescription(fn() => 'هل أنت متأكد من قبض المبلغ المتبقي (' . number_format($this->ownerRecord->remaining_amount, 2) . ' جنيه)؟'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد اذون قبض')
            ->emptyStateDescription('لم يتم إنشاء أي اذون قبض لهذا الإذن بعد.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}

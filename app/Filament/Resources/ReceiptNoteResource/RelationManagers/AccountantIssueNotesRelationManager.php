<?php

namespace App\Filament\Resources\ReceiptNoteResource\RelationManagers;

use App\Models\AccountantIssueNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountantIssueNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'accountantIssueNotes';

    protected static ?string $title = 'اذون الصرف النقدية';

    protected static ?string $modelLabel = 'اذن صرف نقدية';

    protected static ?string $pluralModelLabel = 'اذون صرف نقدية';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('for_model_id')
                    ->default(fn() => $this->ownerRecord->id),
                Forms\Components\Hidden::make('for_model_type')
                    ->default(fn() => get_class($this->ownerRecord)),
                Forms\Components\TextInput::make('paid')
                    ->label('المبلغ المدفوع')
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(fn() => $this->ownerRecord->remaining_amount)
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
                    ->label('المبلغ المدفوع')
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
                    ->label('اذن صرف جديد')
                    ->icon('heroicon-o-plus')
                    ->visible(fn() => $this->ownerRecord->remaining_amount > 0),
                Tables\Actions\Action::make('pay_left')
                    ->label('دفع المتبقي')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn() => $this->ownerRecord->remaining_amount > 0)
                    ->action(function () {
                        AccountantIssueNote::create([
                            'for_model_id' => $this->ownerRecord->id,
                            'for_model_type' => get_class($this->ownerRecord),
                            'paid' => $this->ownerRecord->remaining_amount,
                            'notes' => 'دفع المتبقي من إذن الاستلام',
                        ]);
                        $this->ownerRecord->refresh();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد دفع المتبقي')
                    ->modalDescription(fn() => 'هل أنت متأكد من دفع المبلغ المتبقي (' . number_format($this->ownerRecord->remaining_amount, 2) . ' جنيه)؟'),
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
            ->emptyStateHeading('لا توجد اذون صرف')
            ->emptyStateDescription('لم يتم إنشاء أي اذون صرف لهذا الإذن بعد.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}

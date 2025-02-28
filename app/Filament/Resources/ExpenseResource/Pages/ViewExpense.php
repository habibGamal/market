<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('موافقة')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->can('approve_expenses'))
                ->action(fn () => $this->record->approve()),
        ];
    }
}

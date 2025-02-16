<?php

namespace App\Filament\Traits;
use Filament\Actions;

trait InvoiceLikeEditActions
{

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->formId('form'),
            Actions\DeleteAction::make(),
            printAction(Actions\Action::make('print'))
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}

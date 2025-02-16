<?php

namespace App\Filament\Traits;
use Filament\Actions;

trait InvoiceLikeCreateActions
{
    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->formId('form'),
            $this->getCreateAnotherFormAction()
                ->formId('form'),
            $this->getCancelFormAction()
                ->formId('form'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}

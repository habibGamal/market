<?php

namespace App\Filament\Traits;
use Filament\Actions;

trait InvoiceLikeEditCloseHandler
{
    protected function afterSave(): void
    {
        if ($this->record->closed) {
            $resource = static::$resource;
            $viewRoute = $resource::getUrl('view', ['record' => $this->record->id]);
            redirect()->to($viewRoute);
        }
    }
}

<?php

namespace App\Filament\Traits;
use Filament\Actions;

trait InvoiceLikeCreateCloseHandler
{
    protected function afterCreate(): void
    {
        if ($this->record->closed) {
            $resource = static::$resource;
            $viewRoute = $resource::getUrl('view', ['record' => $this->record->id]);
            redirect()->to($viewRoute);
        }
    }
}

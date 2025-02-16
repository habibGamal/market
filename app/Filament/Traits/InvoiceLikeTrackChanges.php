<?php

namespace App\Filament\Traits;

trait InvoiceLikeTrackChanges
{
    // This method will be used to track changes in invoice-like models
    protected function beforeSave()
    {
        $this->record->setOldItems($this->record->items->toArray());
    }
}

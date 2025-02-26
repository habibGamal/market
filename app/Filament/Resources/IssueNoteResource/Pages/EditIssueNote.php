<?php

namespace App\Filament\Resources\IssueNoteResource\Pages;

use App\Filament\Resources\IssueNoteResource;
use App\Filament\Traits\InvoiceLikeEditActions;
use App\Filament\Traits\InvoiceLikeEditCloseHandler;
use App\Filament\Traits\InvoiceLikeTrackChanges;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIssueNote extends EditRecord
{
    use InvoiceLikeEditActions, InvoiceLikeEditCloseHandler, InvoiceLikeTrackChanges;
    protected static string $resource = IssueNoteResource::class;

}

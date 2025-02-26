<?php

namespace App\Observers;

use App\Enums\IssueNoteType;
use App\Models\IssueNote;
use App\Services\IssueNoteServices;
use App\Enums\InvoiceStatus;

class IssueNoteObserver
{
    public function updating(IssueNote $issueNote): void
    {
        // Check if status is being changed to CLOSED
        if ($issueNote->isDirty('status') && $issueNote->status === InvoiceStatus::CLOSED) {
            $services = app(IssueNoteServices::class);

            // Handle different types of issue notes
            if ($issueNote->note_type === IssueNoteType::ORDERS) {
                $services->closeOrdersIssueNote($issueNote);
            } elseif ($issueNote->note_type === IssueNoteType::RETURN_PURCHASES) {
                $services->closeReturnPurchaseIssueNote($issueNote);
            } elseif ($issueNote->note_type === IssueNoteType::WASTE) {
                $services->closeWasteIssueNote($issueNote);
            }
        }

        $issueNote->total = $issueNote->items->sum('total');
    }
}

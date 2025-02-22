<?php

namespace App\Observers;

use App\Enums\IssueNoteType;
use App\Models\IssueNote;
use App\Services\IssueNoteServices;
use App\Enums\InvoiceStatus;

class IssueNoteObserver
{
    public function saving(IssueNote $issueNote): void
    {
        // Check if status is being changed to CLOSED
        if ($issueNote->isDirty('status') && $issueNote->status === InvoiceStatus::CLOSED && $issueNote->note_type === IssueNoteType::ORDERS) {
            // Get the service instance and process the closing
            app(IssueNoteServices::class)->closeOrdersIssueNote($issueNote);
        }
    }
}

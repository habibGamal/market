<?php

namespace App\Services;

use App\Models\AccountantReceiptNote;
use App\Models\Driver;
use App\Models\IssueNote;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountantReceiptNoteService
{
    public function handle(AccountantReceiptNote $note): void
    {
        match ($note->from_model_type) {
            Driver::class => $this->handleDriverReceipt($note),
            IssueNote::class => $this->handleIssueNoteReceipt($note),
            default => throw new Exception('نوع المستند غير معروف')
        };
    }

    private function handleDriverReceipt(AccountantReceiptNote $note): void
    {
        $driver = Driver::find($note->from_model_id);

        if ($driver->account->balance < $note->paid) {

            throw new Exception('المبلغ المطلوب تحصيله أكبر من رصيد السائق');
        }

        DB::transaction(function () use ($driver, $note) {
            $driver->account->decrement('balance', $note->paid);
        });
    }

    private function handleIssueNoteReceipt(AccountantReceiptNote $note): void
    {
        $issueNote = IssueNote::find($note->from_model_id);

        if ($issueNote->total !== $note->paid) {

            throw new Exception('المبلغ المحصل لا يساوي إجمالي إذن الصرف');
        }
    }
}

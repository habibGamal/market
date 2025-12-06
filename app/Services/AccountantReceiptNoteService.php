<?php

namespace App\Services;

use App\Models\AccountantReceiptNote;
use App\Models\Driver;
use App\Models\IssueNote;
use App\Models\DriverBalanceTracker;
use App\Enums\DriverBalanceTransactionType;
use App\Enums\BalanceOperation;
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

            throw new Exception('المبلغ المطلوب تحصيله أكبر من رصيد مندوب التسليم');
        }

        DB::transaction(function () use ($driver, $note) {
            // Track the balance change
            DriverBalanceTracker::track(
                driverId: $driver->id,
                transactionType: DriverBalanceTransactionType::RECEIPT,
                operation: BalanceOperation::DECREMENT,
                amount: (float) $note->paid,
                relatedModel: $note,
                description: 'سند صرف محاسب - تحصيل من مندوب التسليم',
                notes: 'رقم السند: ' . $note->id
            );

            $driver->account->decrement('balance', $note->paid);

        });
    }

    private function handleIssueNoteReceipt(AccountantReceiptNote $note): void
    {
        $issueNote = IssueNote::find($note->from_model_id);

        // Get remaining amount using the model's helper method
        $remainingAmount = $issueNote->remaining_amount;

        // Validate that payment doesn't exceed remaining amount
        if ($note->paid > $remainingAmount) {
            throw new Exception('المبلغ المحصل (' . number_format($note->paid, 2) . ' جنيه) لا يمكن أن يتجاوز المبلغ المتبقي (' . number_format($remainingAmount, 2) . ' جنيه)');
        }

        // Validate that payment is positive
        if ($note->paid <= 0) {
            throw new Exception('المبلغ المحصل يجب أن يكون أكبر من صفر');
        }
    }
}

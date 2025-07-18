<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issue_notes', function (Blueprint $table) {
            $table->string('payment_status')->default(PaymentStatus::UNPAID->value)->after('status');
        });

        // Update existing issue notes payment status based on their accountant receipt notes
        $this->updateExistingIssueNotesPaymentStatus();
    }

    /**
     * Update existing issue notes payment status
     */
    private function updateExistingIssueNotesPaymentStatus(): void
    {
        // Get all issue notes of type RETURN_PURCHASES
        $issueNotes = \DB::table('issue_notes')
            ->where('note_type', \App\Enums\IssueNoteType::RETURN_PURCHASES->value)
            ->get();

        foreach ($issueNotes as $issueNote) {
            // Calculate total paid for this issue note
            $totalPaid = \DB::table('accountant_receipt_notes')
                ->where('from_model_type', \App\Models\IssueNote::class)
                ->where('from_model_id', $issueNote->id)
                ->sum('paid');

            // Determine payment status
            $paymentStatus = PaymentStatus::UNPAID->value;

            if ($totalPaid > 0) {
                if ($totalPaid >= $issueNote->total) {
                    $paymentStatus = PaymentStatus::PAID->value;
                } else {
                    $paymentStatus = PaymentStatus::PARTIAL_PAID->value;
                }
            }

            // Update the payment status
            \DB::table('issue_notes')
                ->where('id', $issueNote->id)
                ->update(['payment_status' => $paymentStatus]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_notes', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};

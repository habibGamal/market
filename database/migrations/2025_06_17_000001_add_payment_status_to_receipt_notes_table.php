<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('receipt_notes', function (Blueprint $table) {
            $table->string('payment_status')->default(PaymentStatus::UNPAID->value)->after('status');
        });

        // Update existing receipt notes payment status based on their accountant issue notes
        $this->updateExistingReceiptNotesPaymentStatus();
    }

    /**
     * Update existing receipt notes payment status
     */
    private function updateExistingReceiptNotesPaymentStatus(): void
    {
        // Get all receipt notes of type PURCHASES
        $receiptNotes = \DB::table('receipt_notes')
            ->where('note_type', \App\Enums\ReceiptNoteType::PURCHASES->value)
            ->get();

        foreach ($receiptNotes as $receiptNote) {
            // Calculate total paid for this receipt note
            $totalPaid = \DB::table('accountant_issue_notes')
                ->where('for_model_type', \App\Models\ReceiptNote::class)
                ->where('for_model_id', $receiptNote->id)
                ->sum('paid');

            // Determine payment status
            $paymentStatus = PaymentStatus::UNPAID->value;

            if ($totalPaid > 0) {
                if ($totalPaid >= $receiptNote->total) {
                    $paymentStatus = PaymentStatus::PAID->value;
                } else {
                    $paymentStatus = PaymentStatus::PARTIAL_PAID->value;
                }
            }

            // Update the payment status
            \DB::table('receipt_notes')
                ->where('id', $receiptNote->id)
                ->update(['payment_status' => $paymentStatus]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_notes', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};

<?php

use App\Enums\InvoiceStatus;
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
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->decimal('total', 8, 2);
            $table->string('status')->default(InvoiceStatus::DRAFT->value);
            $table->text('notes')->nullable();
            $table->foreignId('officer_id')->constrained('users');
            $table->foreignId('receipt_note_id')->nullable()->constrained('receipt_notes')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('execution_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};

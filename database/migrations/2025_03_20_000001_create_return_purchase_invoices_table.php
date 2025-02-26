<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->decimal('total', 8, 2);
            $table->foreignId('issue_note_id')->nullable()->constrained('issue_notes')->nullOnDelete();
            $table->string('status')->default(InvoiceStatus::DRAFT->value);
            $table->foreignId('officer_id')->constrained('users');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_purchase_invoices');
    }
};

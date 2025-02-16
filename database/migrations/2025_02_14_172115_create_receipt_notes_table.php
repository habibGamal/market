<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\InvoiceStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->decimal('total', 8, 2);
            $table->string('status')->default(InvoiceStatus::DRAFT);
            $table->string('note_type');
            $table->foreignId('officer_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_notes');
    }
};

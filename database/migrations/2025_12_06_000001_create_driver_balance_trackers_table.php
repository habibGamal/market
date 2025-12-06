<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_balance_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_type'); // delivery, return, receipt, adjustment
            $table->string('operation'); // increment, decrement
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->nullableMorphs('related_model', 'dbt_related_model'); // order_id, return_order_item_id, accountant_receipt_note_id, etc.
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['driver_id', 'created_at'], 'dbt_driver_created_idx');
            $table->index('transaction_type', 'dbt_transaction_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_balance_trackers');
    }
};

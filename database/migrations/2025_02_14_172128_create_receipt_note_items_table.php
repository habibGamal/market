<?php

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
        Schema::create('receipt_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('packets_quantity');
            $table->integer('piece_quantity');
            $table->decimal('packet_cost', 8, 2);
            $table->foreignId('receipt_note_id')->constrained()->cascadeOnDelete();;
            $table->text('release_dates');
            $table->text('reference_state')->nullable();
            $table->decimal('total', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_note_items');
    }
};

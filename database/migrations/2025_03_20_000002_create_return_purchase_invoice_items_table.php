<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->integer('packets_quantity');
            $table->decimal('packet_cost', 8, 2);
            $table->date('release_date');
            $table->foreignId('return_purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_purchase_invoice_items');
    }
};

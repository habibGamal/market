<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancelled_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->integer('packets_quantity')->default(0);
            $table->decimal('packet_price', 10, 2)->default(0);
            $table->decimal('packet_cost', 10, 2)->default(0);
            $table->integer('piece_quantity')->default(0);
            $table->decimal('piece_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->foreignId('officer_id')->constrained('users');
            $table->foreignId('order_id')->constrained();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancelled_order_items');
    }
};

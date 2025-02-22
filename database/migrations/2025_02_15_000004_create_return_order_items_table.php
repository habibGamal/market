<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('packets_quantity')->default(0);
            $table->decimal('packet_price', 10, 2)->default(0);
            $table->decimal('piece_price', 10, 2);
            $table->integer('piece_quantity');
            $table->decimal('total', 10, 2);
            $table->foreignId('driver_id')->nullable()->constrained('users');
            $table->string('return_reason');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_order_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_counting_items', function (Blueprint $table) {
            $table->id();
            $table->bool('is_new')->default(false);
            $table->foreignId('product_id')->constrained();
            $table->integer('old_packets_quantity')->default(0);
            $table->integer('old_piece_quantity')->default(0);
            $table->integer('new_packets_quantity')->default(0);
            $table->integer('new_piece_quantity')->default(0);
            $table->decimal('packet_cost', 10, 2)->default(0);
            $table->foreignId('stock_counting_id')->constrained()->cascadeOnDelete();
            $table->date('release_date');
            $table->decimal('total_diff', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_counting_items');
    }
};

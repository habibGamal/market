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
        Schema::create('product_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('min_packets');
            $table->integer('max_packets');
            $table->integer('min_pieces');
            $table->integer('max_pieces');
            $table->foreignId('area_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_limits');
    }
};

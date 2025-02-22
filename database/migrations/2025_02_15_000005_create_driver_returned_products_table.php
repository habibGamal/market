<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_returned_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users','id');
            $table->foreignId('product_id')->constrained();
            $table->integer('packets_quantity')->default(0);
            $table->integer('piece_quantity')->default(0);
            $table->unique(['driver_id', 'product_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_returned_products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ExpirationUnit; // Make sure to create this enum

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('image')->nullable();
            $table->string('barcode');
            $table->decimal('packet_cost', 8, 2);
            $table->decimal('packet_price', 8, 2);
            $table->decimal('piece_price', 8, 2);
            $table->integer('expiration_duration');
            $table->enum('expiration_unit', ExpirationUnit::values());
            $table->text('before_discount');
            $table->integer('packet_to_piece');
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->unique(['name', 'barcode']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ExpirationUnit;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('image')->nullable();
            $table->string('barcode');
            $table->text('description')->nullable();
            $table->decimal('packet_cost', 8, 2);
            $table->decimal('packet_price', 8, 2);
            $table->decimal('piece_price', 8, 2);
            $table->string('packet_alter_name')->default('كرتونة');
            $table->string('piece_alter_name')->default('علبة');
            $table->integer('expiration_duration');
            $table->enum('expiration_unit', ['day', 'week', 'month', 'year']);
            $table->text('before_discount');
            $table->integer('packet_to_piece');
            $table->integer('min_packets_stock_limit')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('can_sell_pieces')->default(false);
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->unique(['name', 'barcode']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

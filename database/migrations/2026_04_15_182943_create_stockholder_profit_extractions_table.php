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
        Schema::create('stockholder_profit_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stockholder_id')->constrained('stockholders');
            $table->decimal('profit', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('officer_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockholder_profit_extractions');
    }
};

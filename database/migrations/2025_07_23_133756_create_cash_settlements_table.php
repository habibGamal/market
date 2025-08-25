<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_settlements', function (Blueprint $table) {
            $table->id();
            $table->decimal('value', 10, 2);
            $table->text('notes')->nullable();
            $table->string('type'); // in/out - will be handled by enum in PHP
            $table->foreignId('officer_id')->constrained('users')->cascadeOnDelete();
            $table->string('status'); // paid/unpaid - will be handled by enum in PHP
            $table->timestamp('paid_at')->nullable();
            $table->date('should_paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_settlements');
    }
};

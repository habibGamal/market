<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountant_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('from_model');
            $table->decimal('paid', 8, 2);
            $table->text('notes')->nullable();
            $table->foreignId('officer_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountant_receipt_notes');
    }
};

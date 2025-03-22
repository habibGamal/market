<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_countings', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_diff', 10, 2);
            $table->string('status')->default(InvoiceStatus::DRAFT->value);
            $table->foreignId('officer_id')->constrained('users');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_countings');
    }
};

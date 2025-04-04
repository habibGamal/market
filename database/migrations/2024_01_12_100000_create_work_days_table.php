<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_purchase', 10, 2)->default(0);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->decimal('total_assets', 10, 2)->default(0);
            $table->decimal('total_purchase_returnes', 10, 2)->default(0);
            $table->decimal('total_day', 10, 2)->default(0);
            $table->decimal('start_day', 10, 2)->default(0);
            $table->date('day')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_days');
    }
};

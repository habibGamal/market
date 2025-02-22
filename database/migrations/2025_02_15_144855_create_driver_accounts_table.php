<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->unique()->constrained('users');
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_accounts');
    }
};

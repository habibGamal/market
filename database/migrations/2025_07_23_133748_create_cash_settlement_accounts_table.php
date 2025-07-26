<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_settlement_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('inlet_name_alias')->nullable();
            $table->string('outlet_name_alias')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_settlement_accounts');
    }
};

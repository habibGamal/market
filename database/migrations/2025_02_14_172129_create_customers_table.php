<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->foreignId('gov_id')->constrained('govs');
            $table->foreignId('city_id')->constrained('cities');
            $table->string('village')->nullable();
            $table->foreignId('area_id')->constrained('areas');
            $table->text('address');
            $table->string('phone')->unique();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->integer('rating_points')->default(0);
            $table->foreignId('business_type_id')->nullable()->constrained('business_types')->nullOnDelete();
            $table->decimal('postpaid_balance', 10, 2)->default(0);
            $table->boolean('blocked')->default(false);
            $table->timestamp('phone_verified_at')->nullable();
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customers_password_reset_tokens', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customers_password_reset_tokens');
    }
};

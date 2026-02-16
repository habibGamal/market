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
        Schema::table('products', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['name', 'barcode']);

            // Make barcode nullable
            $table->string('barcode')->nullable()->change();

            // Add unique constraint on barcode only (allows multiple NULLs)
            $table->unique('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop the unique constraint on barcode
            $table->dropUnique(['barcode']);

            // Make barcode non-nullable
            $table->string('barcode')->nullable(false)->change();

            // Restore the composite unique constraint
            $table->unique(['name', 'barcode']);
        });
    }
};

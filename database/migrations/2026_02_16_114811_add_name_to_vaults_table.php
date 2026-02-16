<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vaults', function (Blueprint $table) {
            $table->string('name')->default('الخزينة النقدية')->after('id');
        });

        // Update existing vault to have the default name
        DB::table('vaults')->where('id', 1)->update(['name' => 'الخزينة النقدية']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaults', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};

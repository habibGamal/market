<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_manager', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('notification_type')->default('general');
            $table->json('data')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('filters')->nullable();
            $table->timestamp('schedule_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('successful_sent')->default(0);
            $table->integer('failed_sent')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_manager');
    }
};

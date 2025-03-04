<?php

use App\Enums\SectionLocation;
use App\Enums\SectionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('business_type_id')->constrained()->cascadeOnDelete();
            $table->string('location')->default(SectionLocation::HOME->value);
            $table->string('section_type')->default(SectionType::VIRTUAL->value);
            $table->timestamps();
        });

        Schema::create('sectionables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->morphs('sectionable');
            $table->timestamps();

            $table->unique(['section_id', 'sectionable_id', 'sectionable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sectionables');
        Schema::dropIfExists('sections');
    }
};

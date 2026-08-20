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
        Schema::create('version_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vulnerability_id')->constrained()->cascadeOnDelete();
            $table->string('version_start')->nullable();
            $table->boolean('version_start_including')->default(true);
            $table->string('version_end')->nullable();
            $table->boolean('version_end_including')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_ranges');
    }
};

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
        Schema::create('ingest_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->string('external_id');
            $table->jsonb('raw_payload');
            $table->timestamp('fetched_at');
            $table->timestamp('processed_at')->nullable();
            $table->enum('processing_status', ['pending', 'processed', 'failed', 'skipped'])->default('pending');
            $table->text('processing_error')->nullable();
            $table->timestamps();

            $table->unique(['source_id', 'external_id']);
            $table->index('processing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingest_records');
    }
};

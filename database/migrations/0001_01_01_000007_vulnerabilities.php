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
        Schema::create( 'vulnerabilities', function (Blueprint $table) {
            $table->id();
            $table->string('cve_id')->nullable();
            $table->string('cwe_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('cvss_score')->nullable();
            $table->string('cvss_vector')->nullable();
            $table->string('reference_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vulnerabilities');
    }
};

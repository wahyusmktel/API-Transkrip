<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('transcript_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal_kelulusan')->nullable();
            $table->date('tanggal_transkrip')->nullable();
            $table->string('skala_penilaian')->default('0-100');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('transcript_configs');
    }
};

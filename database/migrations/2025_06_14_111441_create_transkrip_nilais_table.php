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
        Schema::create('transkrip_nilais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('mapel_id');
            $table->decimal('nilai', 5, 2);
            $table->string('kelompok')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('master_siswas')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('mata_pelajarans')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transkrip_nilais');
    }
};

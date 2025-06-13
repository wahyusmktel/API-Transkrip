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
        Schema::create('master_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kelas');
            $table->string('tingkat', 10);
            $table->string('jurusan');
            $table->string('wali_kelas');
            $table->unsignedInteger('jumlah_siswa');
            $table->string('tahun_ajaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kelas');
    }
};

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
        Schema::table('master_siswas', function (Blueprint $table) {
            // Buat kolom relasi baru
            $table->uuid('program_keahlian_id')->nullable()->after('nomor_ijazah');
            $table->uuid('kelas_id')->nullable()->after('program_keahlian_id');

            // Hapus kolom string lama jika sudah tidak dipakai
            $table->dropColumn(['program_keahlian', 'kelas']);

            // Foreign key constraints
            $table->foreign('program_keahlian_id')->references('id')->on('program_keahlians')->onDelete('set null');
            $table->foreign('kelas_id')->references('id')->on('master_kelas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('master_siswas', function (Blueprint $table) {
            // Rollback kolom baru
            $table->dropForeign(['program_keahlian_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['program_keahlian_id', 'kelas_id']);

            // Tambah kembali kolom string lama
            $table->string('program_keahlian')->nullable();
            $table->string('kelas')->nullable();
        });
    }
};

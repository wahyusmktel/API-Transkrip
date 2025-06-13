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
        Schema::table('master_kelas', function (Blueprint $table) {
            // Drop kolom jurusan lama
            $table->dropColumn('jurusan');

            // Tambahkan kolom id_program sebagai relasi UUID ke program_keahlians
            $table->uuid('id_program')->after('tingkat')->nullable();

            // Tambahkan foreign key constraint
            $table->foreign('id_program')->references('id')->on('program_keahlians')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('master_kelas', function (Blueprint $table) {
            // Drop foreign key dan kolom id_program
            $table->dropForeign(['id_program']);
            $table->dropColumn('id_program');

            // Tambahkan kembali kolom jurusan jika rollback
            $table->string('jurusan')->nullable();
        });
    }
};

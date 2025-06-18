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
            $table->string('pdf_transkrip_filename')->nullable()->after('no_transkrip');
        });
    }

    public function down(): void
    {
        Schema::table('master_siswas', function (Blueprint $table) {
            $table->dropColumn('pdf_transkrip_filename');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jawaban_siswas', function (Blueprint $table) {
            // Kita coba pakai try-catch biar gak crash kalau index udah ada
            try {
                DB::statement('ALTER TABLE jawaban_siswas ADD INDEX idx_jawaban_ujian_siswa (ujian_id, siswa_id)');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_siswas', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_jawaban_ujian_siswa');
            } catch (\Exception $e) {}
        });
    }
};

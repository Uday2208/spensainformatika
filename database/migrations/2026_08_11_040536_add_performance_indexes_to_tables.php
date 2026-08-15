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
        try {
            DB::statement('ALTER TABLE absensis ADD INDEX absensis_tanggal_index (tanggal)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE absensis ADD INDEX idx_absensi_lookup (siswa_id, tanggal)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE nilais ADD INDEX idx_nilai_lookup (siswa_id, bab)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE penilaian_harians ADD INDEX idx_ph_lookup (siswa_id, tanggal, kelas_id)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE artikels ADD INDEX artikels_slug_index (slug)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE ujians ADD INDEX ujians_status_index (status)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE hasil_ujians ADD UNIQUE INDEX idx_hasil_ujian_unique (ujian_id, siswa_id)');
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex('idx_absensi_lookup');
        });

        Schema::table('nilais', function (Blueprint $table) {
            $table->dropIndex('idx_nilai_lookup');
        });

        Schema::table('penilaian_harians', function (Blueprint $table) {
            $table->dropIndex('idx_ph_lookup');
        });

        Schema::table('artikels', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['status']);
        });

        Schema::table('ujians', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->dropUnique('idx_hasil_ujian_unique');
        });
    }
};

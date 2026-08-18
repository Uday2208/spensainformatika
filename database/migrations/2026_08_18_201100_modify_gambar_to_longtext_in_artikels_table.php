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
        if (Schema::hasTable('artikels') && Schema::hasColumn('artikels', 'gambar')) {
            try {
                DB::statement('ALTER TABLE artikels MODIFY gambar LONGTEXT NULL');
            } catch (\Exception $e) {
                // Fallback using Schema builder
                Schema::table('artikels', function (Blueprint $table) {
                    $table->longText('gambar')->nullable()->change();
                });
            }
        }

        if (Schema::hasTable('materis') && Schema::hasColumn('materis', 'foto')) {
            try {
                DB::statement('ALTER TABLE materis MODIFY foto LONGTEXT NULL');
                DB::statement('ALTER TABLE materis MODIFY file_materi LONGTEXT NULL');
            } catch (\Exception $e) {
                // Safe ignore if already modified
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('artikels') && Schema::hasColumn('artikels', 'gambar')) {
            try {
                DB::statement('ALTER TABLE artikels MODIFY gambar TEXT NULL');
            } catch (\Exception $e) {
                // Safe ignore
            }
        }
    }
};

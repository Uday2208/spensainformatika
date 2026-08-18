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
        try {
            Schema::table('nilais', function (Blueprint $table) {
                $table->unique(['siswa_id', 'bab'], 'nilais_siswa_id_bab_unique');
            });
        } catch (\Exception $e) {
            // Index already exists on live database
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropUnique('nilais_siswa_id_bab_unique');
        });
    }
};

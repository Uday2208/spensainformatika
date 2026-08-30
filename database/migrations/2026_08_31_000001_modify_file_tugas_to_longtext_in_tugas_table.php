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
        if (Schema::hasTable('tugas') && Schema::hasColumn('tugas', 'file_tugas')) {
            try {
                DB::statement('ALTER TABLE tugas MODIFY file_tugas LONGTEXT NULL');
            } catch (\Exception $e) {
                Schema::table('tugas', function (Blueprint $table) {
                    $table->longText('file_tugas')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tugas') && Schema::hasColumn('tugas', 'file_tugas')) {
            try {
                DB::statement('ALTER TABLE tugas MODIFY file_tugas VARCHAR(255) NULL');
            } catch (\Exception $e) {
                Schema::table('tugas', function (Blueprint $table) {
                    $table->string('file_tugas')->nullable()->change();
                });
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom ulangan ke nilais (AMAN – hanya Schema::table)
        if (!Schema::hasColumn('nilais', 'ulangan')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->decimal('ulangan', 5, 2)->default(0)->after('proyek');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilais', 'ulangan')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->dropColumn('ulangan');
            });
        }
    }
};

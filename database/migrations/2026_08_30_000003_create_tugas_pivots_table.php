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
        if (!Schema::hasTable('tugas_kelas')) {
            Schema::create('tugas_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
                $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
                $table->unique(['tugas_id', 'kelas_id']);
            });
        }

        if (!Schema::hasTable('tugas_siswa')) {
            Schema::create('tugas_siswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
                $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
                $table->unique(['tugas_id', 'siswa_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_siswa');
        Schema::dropIfExists('tugas_kelas');
    }
};

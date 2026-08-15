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
        Schema::dropIfExists('penilaian_harians');
        Schema::create('penilaian_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('pertemuan');
            $table->integer('nilai')->default(80);
            $table->string('catatan')->nullable();
            $table->timestamps();

            // Constraint: UNIQUE (siswa_id, tanggal, pertemuan)
            $table->unique(['siswa_id', 'tanggal', 'pertemuan'], 'penilaian_harians_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_harians');
    }
};

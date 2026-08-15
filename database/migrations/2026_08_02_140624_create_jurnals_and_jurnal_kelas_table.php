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
        // Hapus tabel lama jika ada
        Schema::dropIfExists('jurnal_mengajars');

        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->date('tanggal');
            $table->string('pertemuan')->nullable();
            $table->string('materi')->nullable();
            $table->text('tujuan_pembelajaran')->nullable();
            $table->string('kegiatan')->nullable();
            $table->text('catatan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();
        });

        Schema::create('jurnal_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnals')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['jurnal_id', 'kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_kelas');
        Schema::dropIfExists('jurnals');
    }
};

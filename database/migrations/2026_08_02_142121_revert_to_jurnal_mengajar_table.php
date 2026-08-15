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
        Schema::dropIfExists('jurnal_kelas');
        Schema::dropIfExists('jurnals');

        Schema::create('jurnal_mengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('guru_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('pertemuan');
            $table->string('materi')->nullable();
            $table->text('tujuan_pembelajaran')->nullable();
            $table->string('kegiatan')->nullable();
            $table->text('catatan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();

            $table->unique(['kelas_id', 'tanggal', 'pertemuan'], 'jurnal_mengajars_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_mengajars');
    }
};

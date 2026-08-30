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
        if (!Schema::hasTable('tugas')) {
            Schema::create('tugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
                $table->string('judul');
                $table->text('deskripsi')->nullable();
                $table->enum('tipe_target', ['kelas', 'individu'])->default('kelas');
                $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('cascade');
                $table->foreignId('siswa_id')->nullable()->constrained('siswas')->onDelete('cascade');
                $table->dateTime('deadline')->nullable();
                $table->string('file_tugas')->nullable();
                $table->string('link')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};

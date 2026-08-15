<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel ujians
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('bab');
            $table->date('tanggal')->nullable();
            $table->integer('durasi')->default(60); // menit
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->string('token', 6)->nullable();
            $table->timestamp('token_expired_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('bab');
        });

        // 2. Tabel pivot ujian_kelas
        Schema::create('ujian_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');

            $table->unique(['ujian_id', 'kelas_id']);
        });

        // 3. Tabel soals
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->enum('tipe', ['pg', 'essay'])->default('pg');
            $table->text('pertanyaan');
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->string('jawaban_benar')->nullable(); // a, b, c, d
            $table->integer('bobot')->default(1);
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index('ujian_id');
        });

        // 4. Tabel jawaban_siswas
        Schema::create('jawaban_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('soals')->onDelete('cascade');
            $table->text('jawaban')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();

            $table->index('siswa_id');
            $table->index('ujian_id');
            $table->index('soal_id');
            $table->unique(['siswa_id', 'soal_id']);
        });

        // 5. Tabel hasil_ujians
        Schema::create('hasil_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->decimal('nilai_pg', 5, 2)->default(0);
            $table->decimal('nilai_essay', 5, 2)->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->enum('status', ['mengerjakan', 'selesai', 'dinilai'])->default('mengerjakan');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('tab_switch_count')->default(0);
            $table->timestamps();

            $table->unique(['siswa_id', 'ujian_id']);
            $table->index('siswa_id');
            $table->index('ujian_id');
        });

        // 6. Tabel log_ujians
        Schema::create('log_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->string('event'); // tab_switch, minimize, auto_submit, warning
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['siswa_id', 'ujian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_ujians');
        Schema::dropIfExists('hasil_ujians');
        Schema::dropIfExists('jawaban_siswas');
        Schema::dropIfExists('soals');
        Schema::dropIfExists('ujian_kelas');
        Schema::dropIfExists('ujians');
    }
};

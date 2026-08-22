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
        if (!Schema::hasTable('ai_koreksi_essays')) {
            Schema::create('ai_koreksi_essays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jawaban_siswa_id')->constrained('jawaban_siswas')->onDelete('cascade');
                $table->string('provider', 50)->default('openai');
                $table->string('model', 100);
                $table->unsignedTinyInteger('score')->default(0); // 0, 5, 10, 15, 20
                $table->unsignedTinyInteger('max_score')->default(20);
                $table->decimal('score_percentage', 5, 2)->default(0.00); // e.g. 75.00
                $table->text('reason')->nullable();
                $table->json('strengths')->nullable();
                $table->json('weaknesses')->nullable();
                $table->text('feedback')->nullable();
                $table->decimal('confidence', 4, 2)->default(0.00); // 0.00 - 1.00
                $table->enum('status', ['pending', 'processing', 'review', 'approved', 'rejected', 'failed'])->default('review');
                $table->json('raw_response')->nullable();
                $table->text('error_message')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index('jawaban_siswa_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_koreksi_essays');
    }
};

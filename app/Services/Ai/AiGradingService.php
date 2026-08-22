<?php

namespace App\Services\Ai;

use App\Models\JawabanSiswa;
use App\Models\AiKoreksiEssay;
use App\Services\Ai\Contracts\AiProviderInterface;
use App\Services\Ai\Providers\OpenAiProvider;
use App\Services\Ai\Providers\GeminiProvider;
use Illuminate\Support\Facades\Log;
use Exception;

class AiGradingService
{
    protected AiProviderInterface $provider;

    public const ALLOWED_SCORES = [0, 5, 10, 15, 20];
    public const MAX_RUBRIC_SCORE = 20;

    public const DEFAULT_RUBRIC = <<<RUBRIC
Skor 20: Jawaban sangat tepat, lengkap, relevan, analitis, dan memenuhi hampir seluruh indikator.
Skor 15: Jawaban benar dan relevan tetapi masih terdapat kekurangan.
Skor 10: Sebagian konsep benar tetapi masih terbatas atau terdapat beberapa kesalahan.
Skor 5: Jawaban sangat terbatas, kurang tepat, atau hanya menunjukkan sedikit pemahaman.
Skor 0: Tidak menjawab, tidak relevan, atau sepenuhnya salah.
RUBRIC;

    public function __construct(?AiProviderInterface $provider = null)
    {
        $this->provider = $provider ?? $this->resolveProvider();
    }

    /**
     * Resolve AI provider berdasarkan konfigurasi.
     */
    protected function resolveProvider(): AiProviderInterface
    {
        $providerName = config('services.ai.provider', 'openai');

        return match ($providerName) {
            'gemini' => new GeminiProvider(),
            'openai' => new OpenAiProvider(),
            default => new OpenAiProvider(),
        };
    }

    /**
     * Mengecek apakah fitur AI grading aktif.
     */
    public function isEnabled(): bool
    {
        return (bool) config('services.ai.enabled', false);
    }

    /**
     * Menghitung persentase skala 100 secara deterministik dari skor rubrik skala 20.
     *
     * @param int $score Skor rubrik (0, 5, 10, 15, 20)
     * @param int $maxScore Skor maksimal rubrik (20)
     * @return float Nilai skala 100 (0, 25, 50, 75, 100)
     */
    public static function calculateScorePercentage(int $score, int $maxScore = self::MAX_RUBRIC_SCORE): float
    {
        if ($maxScore <= 0) {
            return 0.00;
        }

        return round(($score / $maxScore) * 100, 2);
    }

    /**
     * Memproses koreksi 1 jawaban essay siswa dengan AI.
     *
     * @param JawabanSiswa $jawabanSiswa
     * @param int|null $authUserId ID user guru yang memicu koreksi
     * @return AiKoreksiEssay
     * @throws Exception
     */
    public function gradeJawaban(JawabanSiswa $jawabanSiswa, ?int $authUserId = null): AiKoreksiEssay
    {
        if (!$this->isEnabled()) {
            throw new Exception('Fitur AI Koreksi Essay saat ini dinonaktifkan di pengaturan server.');
        }

        // Validasi soal bertipe essay
        $soal = $jawabanSiswa->soal;
        if (!$soal || !$soal->isEssay()) {
            throw new Exception('Jawaban ini bukan merupakan soal bertipe essay/uraian.');
        }

        $jawabanText = trim((string) $jawabanSiswa->jawaban);
        if ($jawabanText === '') {
            throw new Exception('Siswa tidak mengisi jawaban untuk soal essay ini.');
        }

        $providerName = config('services.ai.provider', 'openai');
        $modelName = $providerName === 'gemini'
            ? (string) config('services.ai.gemini.model', 'gemini-1.5-flash')
            : (string) config('services.ai.openai.model', 'gpt-4o-mini');

        $rubrik = self::DEFAULT_RUBRIC;
        $pertanyaan = $soal->pertanyaan;
        $indikator = null; // Masa depan: $soal->indikator jika ditambahkan

        $startTime = microtime(true);

        try {
            // Panggil provider
            $rawResult = $this->provider->gradeEssay($pertanyaan, $indikator, $rubrik, $jawabanText);

            // Validasi Output AI
            $validated = $this->validateAiOutput($rawResult);

            $score = $validated['score'];
            $maxScore = $validated['max_score'];
            $scorePercentage = self::calculateScorePercentage($score, $maxScore);

            $duration = round(microtime(true) - $startTime, 2);

            // Simpan record baru sebagai riwayat (tidak menimpa record lama)
            $aiKoreksi = AiKoreksiEssay::create([
                'jawaban_siswa_id' => $jawabanSiswa->id,
                'provider' => $providerName,
                'model' => $modelName,
                'score' => $score,
                'max_score' => $maxScore,
                'score_percentage' => $scorePercentage,
                'reason' => $validated['reason'],
                'strengths' => $validated['strengths'],
                'weaknesses' => $validated['weaknesses'],
                'feedback' => $validated['feedback'],
                'confidence' => $validated['confidence'],
                'status' => 'review',
                'raw_response' => $rawResult['raw_response'] ?? null,
            ]);

            Log::info('AI Grading Success', [
                'jawaban_siswa_id' => $jawabanSiswa->id,
                'guru_id' => $authUserId,
                'provider' => $providerName,
                'model' => $modelName,
                'score' => $score,
                'score_percentage' => $scorePercentage,
                'confidence' => $validated['confidence'],
                'duration_seconds' => $duration,
            ]);

            return $aiKoreksi;

        } catch (Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);

            Log::error('AI Grading Failed', [
                'jawaban_siswa_id' => $jawabanSiswa->id,
                'guru_id' => $authUserId,
                'provider' => $providerName,
                'model' => $modelName,
                'duration_seconds' => $duration,
                'error_message' => $e->getMessage(),
            ]);

            // Catat kegagalan ke database agar terlihat statusnya tanpa merusak nilai existing
            $failedRecord = AiKoreksiEssay::create([
                'jawaban_siswa_id' => $jawabanSiswa->id,
                'provider' => $providerName,
                'model' => $modelName,
                'score' => 0,
                'max_score' => self::MAX_RUBRIC_SCORE,
                'score_percentage' => 0,
                'reason' => 'Koreksi AI gagal: ' . $e->getMessage(),
                'strengths' => [],
                'weaknesses' => [],
                'feedback' => 'Silakan lakukan koreksi manual atau ulangi koreksi AI beberapa saat lagi.',
                'confidence' => 0.00,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Memvalidasi struktur dan rentang nilai dari output AI.
     *
     * @param array $output
     * @return array
     * @throws Exception
     */
    protected function validateAiOutput(array $output): array
    {
        if (!isset($output['score']) || !in_array((int) $output['score'], self::ALLOWED_SCORES, true)) {
            $scoreVal = $output['score'] ?? 'null';
            throw new Exception("Output AI menghasilkan skor tidak valid ($scoreVal). Skor harus salah satu dari: " . implode(', ', self::ALLOWED_SCORES));
        }

        $score = (int) $output['score'];
        $maxScore = (int) ($output['max_score'] ?? self::MAX_RUBRIC_SCORE);
        if ($maxScore !== self::MAX_RUBRIC_SCORE) {
            $maxScore = self::MAX_RUBRIC_SCORE;
        }

        $confidence = isset($output['confidence']) ? (float) $output['confidence'] : 0.85;
        if ($confidence < 0.0 || $confidence > 1.0) {
            $confidence = max(0.0, min(1.0, $confidence));
        }

        $reason = isset($output['reason']) ? trim((string) $output['reason']) : 'Penilaian sesuai kriteria rubrik.';
        if ($reason === '') {
            $reason = 'Penilaian sesuai kriteria rubrik.';
        }

        $strengths = isset($output['strengths']) && is_array($output['strengths']) ? array_values(array_filter($output['strengths'])) : [];
        $weaknesses = isset($output['weaknesses']) && is_array($output['weaknesses']) ? array_values(array_filter($output['weaknesses'])) : [];
        $feedback = isset($output['feedback']) ? trim((string) $output['feedback']) : 'Pertahankan pemahaman konsep dan lengkapi rincian penjelasan.';

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'confidence' => round($confidence, 2),
            'reason' => $reason,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'feedback' => $feedback,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\JawabanSiswa;
use App\Models\AiKoreksiEssay;
use App\Models\HasilUjian;
use App\Models\Ujian;
use App\Services\Ai\AiGradingService;
use Exception;

class AiKoreksiController extends Controller
{
    protected AiGradingService $aiService;

    public function __construct(AiGradingService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Memproses koreksi 1 jawaban essay menggunakan AI.
     * Endpoint: POST /app/ujian/jawaban/{jawabanId}/koreksi-ai
     */
    public function gradeSingle(Request $request, $jawabanId)
    {
        try {
            $jawabanSiswa = JawabanSiswa::with(['soal', 'ujian'])->findOrFail($jawabanId);

            // Validasi otorisasi: hanya ujian yang sudah selesai yang bisa dikoreksi
            if ($jawabanSiswa->ujian && !$jawabanSiswa->ujian->isSelesai()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ujian belum selesai. Harap akhiri ujian sebelum melakukan koreksi.',
                ], 422);
            }

            $aiKoreksi = $this->aiService->gradeJawaban($jawabanSiswa, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => 'Koreksi AI berhasil diperoleh.',
                'data' => [
                    'id' => $aiKoreksi->id,
                    'jawaban_siswa_id' => $aiKoreksi->jawaban_siswa_id,
                    'score' => $aiKoreksi->score,
                    'max_score' => $aiKoreksi->max_score,
                    'score_percentage' => (float) $aiKoreksi->score_percentage,
                    'reason' => $aiKoreksi->reason,
                    'strengths' => $aiKoreksi->strengths ?? [],
                    'weaknesses' => $aiKoreksi->weaknesses ?? [],
                    'feedback' => $aiKoreksi->feedback,
                    'confidence' => (float) $aiKoreksi->confidence,
                    'confidence_percent' => round(((float) $aiKoreksi->confidence) * 100),
                    'status' => $aiKoreksi->status,
                    'model' => $aiKoreksi->model,
                    'created_at' => $aiKoreksi->created_at->format('d M Y H:i'),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Guru menerima rekomendasi skor AI.
     * Endpoint: POST /app/ujian/koreksi-ai/{id}/accept
     */
    public function acceptScore(Request $request, $id)
    {
        $aiKoreksi = AiKoreksiEssay::with(['jawabanSiswa.ujian', 'jawabanSiswa.soal'])->findOrFail($id);
        $jawabanSiswa = $aiKoreksi->jawabanSiswa;

        if (!$jawabanSiswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data jawaban siswa tidak ditemukan.',
            ], 404);
        }

        $ujian = $jawabanSiswa->ujian;
        $siswaId = $jawabanSiswa->siswa_id;

        DB::beginTransaction();
        try {
            // Tandai AI Koreksi sebagai approved oleh guru
            $aiKoreksi->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Ambil score_percentage sebagai nilai essay rekomendasi (0-100)
            $acceptedScore = (float) $aiKoreksi->score_percentage;

            // Integrasi ke HasilUjian menggunakan business logic existing
            $hasil = HasilUjian::where('ujian_id', $ujian->id)
                ->where('siswa_id', $siswaId)
                ->first();

            $nilaiAkhir = null;
            if ($hasil) {
                $totalBobot = $ujian->totalBobot();
                $bobotPg = $ujian->soalPg()->sum('bobot');
                $bobotEssay = $ujian->soalEssay()->sum('bobot');

                // Update nilai_essay siswa
                if ($totalBobot > 0 && $bobotEssay > 0) {
                    $nilaiAkhir = (($hasil->nilai_pg * $bobotPg) + ($acceptedScore * $bobotEssay)) / $totalBobot;
                } else {
                    $nilaiAkhir = $hasil->nilai_pg;
                }

                $hasil->update([
                    'nilai_essay' => $acceptedScore,
                    'nilai_akhir' => round($nilaiAkhir, 2),
                    'status' => 'dinilai',
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Skor rekomendasi AI berhasil diterima dan diterapkan.',
                'data' => [
                    'ai_koreksi_id' => $aiKoreksi->id,
                    'accepted_score' => $acceptedScore,
                    'score_rubrik' => $aiKoreksi->score,
                    'nilai_akhir' => $nilaiAkhir !== null ? round($nilaiAkhir, 2) : null,
                    'status' => 'approved',
                    'reviewed_by' => Auth::user()->name ?? 'Guru',
                    'reviewed_at' => $aiKoreksi->reviewed_at?->format('d M Y H:i'),
                ],
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerima skor AI: ' . $e->getMessage(),
            ], 500);
        }
    }
}

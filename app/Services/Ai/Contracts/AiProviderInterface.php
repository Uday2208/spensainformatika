<?php

namespace App\Services\Ai\Contracts;

interface AiProviderInterface
{
    /**
     * Mengoreksi jawaban essay siswa menggunakan AI provider.
     *
     * @param string $pertanyaan Pertanyaan soal essay
     * @param string|null $indikator Indikator capaian/kunci konsep soal (opsional)
     * @param string $rubrik Rubrik penilaian (misal skala 20/15/10/5/0)
     * @param string $jawaban Teks jawaban siswa (untrusted data)
     * @return array Hasil terstruktur [score, max_score, reason, strengths, weaknesses, feedback, confidence, raw_response]
     * @throws \Exception Jika request gagal atau output tidak valid
     */
    public function gradeEssay(string $pertanyaan, ?string $indikator, string $rubrik, string $jawaban): array;
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\JawabanSiswa;
use App\Models\HasilUjian;
use App\Models\AiKoreksiEssay;
use App\Services\Ai\AiGradingService;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Config;
use Exception;

class AiKoreksiTest extends TestCase
{
    use RefreshDatabase;

    protected User $guru;
    protected User $siswaUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected Ujian $ujian;
    protected Soal $soalEssay;
    protected Soal $soalPg;
    protected JawabanSiswa $jawabanEssay;
    protected HasilUjian $hasilUjian;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test classes and users
        $this->kelas = Kelas::create([
            'nama_kelas' => '9A',
            'tingkat' => '9',
        ]);

        $this->guru = User::create([
            'name' => 'Guru Penguji',
            'username' => 'guru_test_' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->siswaUser = User::create([
            'name' => 'Siswa Test',
            'username' => 'siswa_test_' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);

        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'kelas_id' => $this->kelas->id,
            'nis' => '12345' . rand(100, 999),
        ]);

        $this->ujian = Ujian::create([
            'judul' => 'Ujian Komputasi',
            'bab' => 'Bab 1',
            'tanggal' => now()->toDateString(),
            'durasi' => 60,
            'status' => 'selesai',
        ]);
        $this->ujian->kelasList()->attach($this->kelas->id);

        $this->soalPg = Soal::create([
            'ujian_id' => $this->ujian->id,
            'tipe' => 'pg',
            'pertanyaan' => 'Apa itu CPU?',
            'opsi_a' => 'Central Processing Unit',
            'opsi_b' => 'Central Power Unit',
            'opsi_c' => 'Computer Personal Unit',
            'opsi_d' => 'Control Program Unit',
            'jawaban_benar' => 'a',
            'bobot' => 10,
            'urutan' => 1,
        ]);

        $this->soalEssay = Soal::create([
            'ujian_id' => $this->ujian->id,
            'tipe' => 'essay',
            'pertanyaan' => 'Jelaskan perbedaan RAM dan ROM serta fungsinya dalam komputer!',
            'bobot' => 10,
            'urutan' => 2,
        ]);

        $this->jawabanEssay = JawabanSiswa::create([
            'siswa_id' => $this->siswa->id,
            'ujian_id' => $this->ujian->id,
            'soal_id' => $this->soalEssay->id,
            'jawaban' => 'RAM bersifat volatile dan menyimpan data sementara, sedangkan ROM bersifat non-volatile dan menyimpan firmware dasar.',
            'is_correct' => null,
        ]);

        $this->hasilUjian = HasilUjian::create([
            'siswa_id' => $this->siswa->id,
            'ujian_id' => $this->ujian->id,
            'nilai_pg' => 100,
            'nilai_essay' => 0,
            'nilai_akhir' => 50,
            'status' => 'selesai',
        ]);
    }

    /**
     * TEST 1: Score conversion from rubric (0-20) to percentage (0-100).
     */
    public function test_score_conversion_deterministik(): void
    {
        $this->assertEquals(100.0, AiGradingService::calculateScorePercentage(20, 20));
        $this->assertEquals(75.0, AiGradingService::calculateScorePercentage(15, 20));
        $this->assertEquals(50.0, AiGradingService::calculateScorePercentage(10, 20));
        $this->assertEquals(25.0, AiGradingService::calculateScorePercentage(5, 20));
        $this->assertEquals(0.0, AiGradingService::calculateScorePercentage(0, 20));
    }

    /**
     * TEST 2: AI Disabled mode returns clean error without crashing.
     */
    public function test_ai_disabled_mode(): void
    {
        Config::set('services.ai.enabled', false);

        $response = $this->actingAs($this->guru)
            ->postJson("/app/ujian/jawaban/{$this->jawabanEssay->id}/koreksi-ai");

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Fitur AI Koreksi Essay saat ini dinonaktifkan di pengaturan server.',
        ]);
    }

    /**
     * TEST 3: Siswa cannot access AI grading endpoint (Authorization).
     */
    public function test_unauthorized_siswa_cannot_access_ai_endpoint(): void
    {
        Config::set('services.ai.enabled', true);

        $response = $this->actingAs($this->siswaUser)
            ->postJson(route('guru.ujian.koreksi-ai', ['jawabanId' => $this->jawabanEssay->id]));

        // RoleMiddleware redirects unauthorized roles to '/'
        $response->assertRedirect('/');
    }

    /**
     * TEST 4: Empty student answer is rejected.
     */
    public function test_empty_answer_is_rejected(): void
    {
        Config::set('services.ai.enabled', true);

        $this->jawabanEssay->update(['jawaban' => '   ']);

        $response = $this->actingAs($this->guru)
            ->postJson(route('guru.ujian.koreksi-ai', ['jawabanId' => $this->jawabanEssay->id]));

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Siswa tidak mengisi jawaban untuk soal essay ini.',
        ]);
    }

    /**
     * TEST 5: Guru can run AI grading with mock provider and receives rubric score 15/20 -> 75%.
     */
    public function test_guru_can_grade_essay_with_ai(): void
    {
        Config::set('services.ai.enabled', true);

        // Mock AiProviderInterface
        $mockProvider = $this->createMock(AiProviderInterface::class);
        $mockProvider->expects($this->once())
            ->method('gradeEssay')
            ->willReturn([
                'score' => 15,
                'max_score' => 20,
                'reason' => 'Konsep perbedaan volatile dan non-volatile dijelaskan dengan tepat.',
                'strengths' => ['Penjelasan akurat', 'Menyebutkan sifat memori'],
                'weaknesses' => ['Contoh penggunaan konkret belum dituliskan'],
                'feedback' => 'Bagus, tambahkan contoh seperti BIOS pada ROM.',
                'confidence' => 0.92,
                'raw_response' => ['mock' => true],
            ]);

        $service = new AiGradingService($mockProvider);
        $this->app->instance(AiGradingService::class, $service);

        $response = $this->actingAs($this->guru)
            ->postJson(route('guru.ujian.koreksi-ai', ['jawabanId' => $this->jawabanEssay->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'score' => 15,
                'max_score' => 20,
                'score_percentage' => 75.0,
                'confidence' => 0.92,
                'confidence_percent' => 92,
                'status' => 'review',
            ],
        ]);

        $this->assertDatabaseHas('ai_koreksi_essays', [
            'jawaban_siswa_id' => $this->jawabanEssay->id,
            'score' => 15,
            'max_score' => 20,
            'score_percentage' => 75.0,
            'status' => 'review',
        ]);
    }

    /**
     * TEST 6: Guru accepts AI score -> updates HasilUjian using existing weighted formula.
     */
    public function test_accept_ai_score_updates_hasil_ujian(): void
    {
        $aiKoreksi = AiKoreksiEssay::create([
            'jawaban_siswa_id' => $this->jawabanEssay->id,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'score' => 15,
            'max_score' => 20,
            'score_percentage' => 75.0,
            'reason' => 'Jawaban benar.',
            'strengths' => ['Tepat'],
            'weaknesses' => [],
            'feedback' => 'Pertahankan',
            'confidence' => 0.90,
            'status' => 'review',
        ]);

        $response = $this->actingAs($this->guru)
            ->postJson(route('guru.ujian.koreksi-ai.accept', ['id' => $aiKoreksi->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'accepted_score' => 75.0,
                'status' => 'approved',
            ],
        ]);

        // HasilUjian: bobot PG = 10, bobot Essay = 10 -> totalBobot = 20
        // Nilai PG = 100, Nilai Essay = 75 -> Nilai Akhir = ((100*10) + (75*10)) / 20 = 87.5
        $this->hasilUjian->refresh();
        $this->assertEquals(75.0, (float) $this->hasilUjian->nilai_essay);
        $this->assertEquals(87.5, (float) $this->hasilUjian->nilai_akhir);
        $this->assertEquals('dinilai', $this->hasilUjian->status);

        $aiKoreksi->refresh();
        $this->assertEquals('approved', $aiKoreksi->status);
        $this->assertEquals($this->guru->id, $aiKoreksi->reviewed_by);
    }

    /**
     * TEST 7: Manual override preserves AI score history and updates final score.
     */
    public function test_manual_override_preserves_ai_history(): void
    {
        $aiKoreksi = AiKoreksiEssay::create([
            'jawaban_siswa_id' => $this->jawabanEssay->id,
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'score' => 15,
            'max_score' => 20,
            'score_percentage' => 75.0,
            'reason' => 'Jawaban benar.',
            'confidence' => 0.90,
            'status' => 'review',
        ]);

        // Guru manually overrides score to 85 via existing endpoint
        $response = $this->actingAs($this->guru)
            ->from(route('guru.hasil.detail-siswa', [$this->ujian->id, $this->siswa->id]))
            ->post(route('guru.hasil.update-siswa', [$this->ujian->id, $this->siswa->id]), [
                'nilai_pg' => 100,
                'nilai_essay' => 85,
            ]);

        $response->assertSessionHas('success');

        $this->hasilUjian->refresh();
        $this->assertEquals(85.0, (float) $this->hasilUjian->nilai_essay);
        // ((100*10) + (85*10)) / 20 = 92.5
        $this->assertEquals(92.5, (float) $this->hasilUjian->nilai_akhir);

        // AI history is still intact with 15/20 (75%)
        $aiKoreksi->refresh();
        $this->assertEquals(15, $aiKoreksi->score);
        $this->assertEquals(75.0, (float) $aiKoreksi->score_percentage);
    }

    /**
     * TEST 8: AI failure does not alter existing student score.
     */
    public function test_ai_failure_does_not_change_existing_scores(): void
    {
        Config::set('services.ai.enabled', true);

        $initialNilaiEssay = $this->hasilUjian->nilai_essay;
        $initialNilaiAkhir = $this->hasilUjian->nilai_akhir;

        $mockProvider = $this->createMock(AiProviderInterface::class);
        $mockProvider->expects($this->once())
            ->method('gradeEssay')
            ->willThrowException(new Exception('Network timeout'));

        $service = new AiGradingService($mockProvider);
        $this->app->instance(AiGradingService::class, $service);

        $response = $this->actingAs($this->guru)
            ->postJson(route('guru.ujian.koreksi-ai', ['jawabanId' => $this->jawabanEssay->id]));

        $response->assertStatus(422);

        $this->hasilUjian->refresh();
        $this->assertEquals($initialNilaiEssay, $this->hasilUjian->nilai_essay);
        $this->assertEquals($initialNilaiAkhir, $this->hasilUjian->nilai_akhir);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;

class WebFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_public_pages()
    {
        $this->get('/')->assertStatus(200);
        $this->get('/artikel')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
    }

    public function test_guru_pages()
    {
        $guru = User::where('username', 'guru')->first();
        
        $response = $this->actingAs($guru)->get('/app/dashboard-guru');
        $response->assertStatus(200);

        $response = $this->actingAs($guru)->get('/app/absensi');
        $response->assertStatus(200);

        $response = $this->actingAs($guru)->get('/app/nilai');
        $response->assertStatus(200);
    }

    public function test_siswa_pages()
    {
        $siswa = User::where('username', 'siswa')->first();
        
        $response = $this->actingAs($siswa)->get('/app/dashboard-siswa');
        $response->assertStatus(200);
    }

    public function test_post_absensi()
    {
        $guru = User::where('username', 'guru')->first();
        $siswaModel = Siswa::first();
        
        $response = $this->actingAs($guru)->post('/app/absensi', [
            'tanggal' => date('Y-m-d'),
            'absensi' => [
                $siswaModel->id => 'hadir'
            ]
        ]);
        
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('absensis', [
            'siswa_id' => $siswaModel->id,
            'status' => 'hadir'
        ]);
    }

    public function test_post_nilai()
    {
        $guru = User::where('username', 'guru')->first();
        $siswaModel = Siswa::first();
        
        $response = $this->actingAs($guru)->post('/app/nilai', [
            'bab' => 'BAB 1',
            'p_harian' => 0,
            'p_tugas' => 30,
            'p_quiz' => 30,
            'p_proyek' => 40,
            'nilai' => [
                $siswaModel->id => [
                    'tugas' => 80,
                    'quiz' => 85,
                    'proyek' => 90
                ]
            ]
        ]);
        
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('nilais', [
            'siswa_id' => $siswaModel->id,
            'bab' => 'BAB 1',
            'nilai_akhir' => (80*0.3) + (85*0.3) + (90*0.4) // 85.5
        ]);
    }
}

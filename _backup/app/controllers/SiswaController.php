<?php

class SiswaController extends Controller {

    public function dashboard() {
        $siswa_id = $_SESSION['siswa_id'] ?? null;
        if (!$siswa_id) {
            die("Akun Anda tidak terkait dengan data siswa manapun.");
        }

        // Get Siswa Info
        $stmtSiswa = $this->pdo->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s 
            JOIN kelas k ON s.kelas_id = k.id 
            WHERE s.id = ?
        ");
        $stmtSiswa->execute([$siswa_id]);
        $siswa = $stmtSiswa->fetch();

        // Get Rekap Absensi
        $stmtAbsen = $this->pdo->prepare("
            SELECT status, COUNT(*) as total 
            FROM absensi 
            WHERE siswa_id = ? 
            GROUP BY status
        ");
        $stmtAbsen->execute([$siswa_id]);
        $absensiRaw = $stmtAbsen->fetchAll();
        
        $rekapAbsen = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
        foreach ($absensiRaw as $row) {
            $rekapAbsen[$row['status']] = $row['total'];
        }

        // Get Rata-rata Keaktifan
        $stmtKeaktifan = $this->pdo->prepare("
            SELECT AVG(nilai_keaktifan) as rata_keaktifan 
            FROM absensi 
            WHERE siswa_id = ? AND status = 'hadir'
        ");
        $stmtKeaktifan->execute([$siswa_id]);
        $rataKeaktifanRaw = $stmtKeaktifan->fetch();
        $rataKeaktifan = $rataKeaktifanRaw ? round($rataKeaktifanRaw['rata_keaktifan'], 1) : 0;

        // Kurikulum Merdeka: Pengolahan Nilai per Mata Pelajaran
        $stmtNilai = $this->pdo->prepare("
            SELECT 
                m.id as mapel_id,
                m.nama as mapel_nama,
                p.jenis_evaluasi,
                p.kategori,
                MAX(n.nilai_akhir) as capaian_terbaik
            FROM penilaian_master p
            JOIN mata_pelajaran m ON p.mata_pelajaran_id = m.id
            JOIN penilaian_nilai n ON p.id = n.penilaian_master_id
            WHERE n.siswa_id = ?
            GROUP BY m.id, m.nama, p.jenis_evaluasi, p.kategori
        ");
        $stmtNilai->execute([$siswa_id]);
        $rawNilai = $stmtNilai->fetchAll();

        // Process Kurikulum Merdeka Logic
        $rapor = [];
        foreach ($rawNilai as $r) {
            $mapel_id = $r['mapel_id'];
            if (!isset($rapor[$mapel_id])) {
                $rapor[$mapel_id] = [
                    'nama' => $r['mapel_nama'],
                    'formatif_pengetahuan' => 0,
                    'sumatif_pengetahuan' => 0,
                    'formatif_keterampilan' => 0,
                    'sumatif_keterampilan' => 0
                ];
            }
            
            $key = strtolower($r['jenis_evaluasi']) . '_' . strtolower($r['kategori']);
            if (isset($rapor[$mapel_id][$key])) {
                $rapor[$mapel_id][$key] = (float)$r['capaian_terbaik'];
            }
        }

        // Calculate Final Score
        foreach ($rapor as $id => $data) {
            // Bobot: 40% Formatif, 60% Sumatif
            $nilai_pengetahuan = (0.4 * $data['formatif_pengetahuan']) + (0.6 * $data['sumatif_pengetahuan']);
            $nilai_keterampilan = (0.4 * $data['formatif_keterampilan']) + (0.6 * $data['sumatif_keterampilan']);
            
            // Rata-rata akhir jika kedua komponen dihitung, jika tidak ada keterampilan misalnya, bisa disesuaikan.
            // Di sini diasumsikan gabungan sederhana, atau kita pecah di view.
            $rapor[$id]['nilai_akhir_pengetahuan'] = round($nilai_pengetahuan, 2);
            $rapor[$id]['nilai_akhir_keterampilan'] = round($nilai_keterampilan, 2);
            $rapor[$id]['nilai_rapor'] = round(($nilai_pengetahuan + $nilai_keterampilan) / 2, 2);
        }

        $this->view('siswa/dashboard', [
            'siswa' => $siswa,
            'rekapAbsen' => $rekapAbsen,
            'rataKeaktifan' => $rataKeaktifan,
            'rapor' => $rapor,
            'title' => 'Dashboard Siswa'
        ]);
    }

    public function gantiPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siswa_id = $_SESSION['siswa_id'] ?? null;
            $password_baru = $_POST['password_baru'] ?? '';
            
            if (!empty($password_baru) && strlen($password_baru) >= 6) {
                $hash = password_hash($password_baru, PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE siswa_id = ?");
                $stmt->execute([$hash, $siswa_id]);
                $_SESSION['success'] = "Password berhasil diubah!";
            } else {
                $_SESSION['error'] = "Password baru minimal 6 karakter.";
            }
            $this->redirect('/app/siswa/dashboard');
        }
    }
}

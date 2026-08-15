<?php

class GuruController extends Controller {

    public function dashboard() {
        // Step 6: Dashboard Guru - Real Data
        $stmtSiswa = $this->pdo->query("SELECT COUNT(*) FROM siswa");
        $totalSiswa = $stmtSiswa->fetchColumn();

        $stmtKelas = $this->pdo->query("SELECT COUNT(*) FROM kelas");
        $totalKelas = $stmtKelas->fetchColumn();

        $today = date('Y-m-d');
        $stmtHadir = $this->pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status = 'hadir'");
        $stmtHadir->execute([$today]);
        $hadirHariIni = $stmtHadir->fetchColumn();

        // Count distinct students who need remedial (nilai akhir < 75)
        // For simplicity right now we just show a dummy or a basic check
        $stmtRemedial = $this->pdo->query("SELECT COUNT(DISTINCT siswa_id) FROM nilai WHERE nilai < 75");
        $siswaRemedial = $stmtRemedial->fetchColumn();

        $this->view('guru/dashboard', [
            'totalSiswa' => $totalSiswa,
            'totalKelas' => $totalKelas,
            'hadirHariIni' => $hadirHariIni,
            'siswaRemedial' => $siswaRemedial,
            'title' => 'Dashboard Guru'
        ]);
    }

    public function kelas() {
        $stmt = $this->pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
        $kelas = $stmt->fetchAll();
        $this->view('guru/kelas', ['kelas' => $kelas, 'title' => 'Manajemen Kelas']);
    }

    public function storeKelas() {
        $nama_kelas = $_POST['nama_kelas'] ?? '';
        if (!empty($nama_kelas)) {
            try {
                $stmt = $this->pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
                $stmt->execute([$nama_kelas]);
                $_SESSION['success'] = "Kelas berhasil ditambahkan.";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Gagal menambah kelas (mungkin duplikat).";
            }
        }
        $this->redirect('/app/guru/kelas');
    }

    public function siswa() {
        $stmt = $this->pdo->query("
            SELECT siswa.*, kelas.nama_kelas 
            FROM siswa 
            JOIN kelas ON siswa.kelas_id = kelas.id 
            ORDER BY siswa.nama
        ");
        $siswa = $stmt->fetchAll();
        
        $stmtKelas = $this->pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
        $kelas = $stmtKelas->fetchAll();
        
        $this->view('guru/siswa', ['siswa' => $siswa, 'kelas' => $kelas, 'title' => 'Manajemen Siswa']);
    }

    public function storeSiswa() {
        $nisn = $_POST['nisn'] ?? '';
        $nama = $_POST['nama'] ?? '';
        $kelas_id = $_POST['kelas_id'] ?? '';
        
        if (!empty($nisn) && !empty($nama) && !empty($kelas_id)) {
            try {
                $this->pdo->beginTransaction();
                
                // Insert Siswa
                $stmt = $this->pdo->prepare("INSERT INTO siswa (nisn, nama, kelas_id) VALUES (?, ?, ?)");
                $stmt->execute([$nisn, $nama, $kelas_id]);
                $siswa_id = $this->pdo->lastInsertId();
                
                // Auto generate akun user siswa
                // username: nisn, password default: nisn
                $username = $nisn;
                $password = password_hash($nisn, PASSWORD_BCRYPT);
                
                $stmtUser = $this->pdo->prepare("INSERT INTO users (username, password, role, siswa_id) VALUES (?, ?, 'siswa', ?)");
                $stmtUser->execute([$username, $password, $siswa_id]);
                
                $this->pdo->commit();
                $_SESSION['success'] = "Siswa dan akun berhasil dibuat (Username: $username, Pass: $username).";
                
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                if ($e->getCode() == '23000') {
                    $_SESSION['error'] = "Gagal menambah siswa. NISN mungkin sudah digunakan.";
                } else {
                    $_SESSION['error'] = "Gagal menambah siswa: " . $e->getMessage();
                }
            }
        } else {
            $_SESSION['error'] = "Semua field (termasuk NISN) harus diisi.";
        }
        $this->redirect('/app/guru/siswa');
    }

    public function absensi() {
        $stmtKelas = $this->pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
        $kelas = $stmtKelas->fetchAll();
        
        $siswa = [];
        $selected_kelas = $_GET['kelas_id'] ?? '';
        if ($selected_kelas) {
            $stmt = $this->pdo->prepare("SELECT * FROM siswa WHERE kelas_id = ? ORDER BY nama");
            $stmt->execute([$selected_kelas]);
            $siswa = $stmt->fetchAll();
        }
        
        $this->view('guru/absensi', [
            'kelas' => $kelas, 
            'siswa' => $siswa, 
            'selected_kelas' => $selected_kelas,
            'title' => 'Input Absensi'
        ]);
    }

    public function storeAbsensi() {
        // Step 8: Transaction + Anti Error
        $kelas_id = $_POST['kelas_id'] ?? '';
        $tanggal = $_POST['tanggal'] ?? '';
        $pertemuan = $_POST['pertemuan'] ?? '';
        $absensi = $_POST['absensi'] ?? [];
        $keterangan = $_POST['keterangan'] ?? [];
        $nilai_keaktifan = $_POST['nilai_keaktifan'] ?? [];

        if (empty($kelas_id) || empty($tanggal) || empty($pertemuan) || empty($absensi)) {
            $_SESSION['error'] = "Pastikan semua field absensi diisi.";
            $this->redirect('/app/guru/absensi?kelas_id=' . $kelas_id);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO absensi (siswa_id, kelas_id, tanggal, pertemuan, status, nilai_keaktifan, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($absensi as $siswa_id => $status) {
                $ket = $keterangan[$siswa_id] ?? '';
                $n_aktif = isset($nilai_keaktifan[$siswa_id]) && $nilai_keaktifan[$siswa_id] !== '' ? (int)$nilai_keaktifan[$siswa_id] : 0;
                $stmt->execute([$siswa_id, $kelas_id, $tanggal, $pertemuan, $status, $n_aktif, $ket]);
            }
            
            $this->pdo->commit();
            $_SESSION['success'] = "Data absensi batch berhasil disimpan.";
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // 23000 is integrity constraint violation (e.g. duplicate unique key)
            if ($e->getCode() == '23000') {
                $_SESSION['error'] = "Gagal menyimpan. Data absensi untuk kelas dan pertemuan ini mungkin sudah ada.";
            } else {
                $_SESSION['error'] = "Gagal menyimpan absensi: " . $e->getMessage();
            }
        }
        
        $this->redirect('/app/guru/absensi?kelas_id=' . $kelas_id);
    }

    public function rekapAbsensi() {
        $stmtKelas = $this->pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
        $kelas = $stmtKelas->fetchAll();
        
        $rekap = [];
        $selected_kelas = $_GET['kelas_id'] ?? '';
        
        if ($selected_kelas) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.id, s.nisn, s.nama,
                    SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                    SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit,
                    SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as total_izin,
                    SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as total_alpha
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id
                WHERE s.kelas_id = ?
                GROUP BY s.id, s.nisn, s.nama
                ORDER BY s.nama
            ");
            $stmt->execute([$selected_kelas]);
            $rekap = $stmt->fetchAll();
        }
        
        $this->view('guru/rekap_absensi', [
            'kelas' => $kelas, 
            'rekap' => $rekap, 
            'selected_kelas' => $selected_kelas,
            'title' => 'Rekap Kehadiran'
        ]);
    }

    // --- PENILAIAN KURIKULUM MERDEKA ---

    public function penilaian() {
        $stmtKelas = $this->pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
        $kelas = $stmtKelas->fetchAll();
        
        $stmtMapel = $this->pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama");
        $mapel = $stmtMapel->fetchAll();
        
        $stmtTA = $this->pdo->query("SELECT * FROM tahun_ajaran ORDER BY id DESC");
        $ta = $stmtTA->fetchAll();
        
        $penilaian_list = [];
        $selected_kelas = $_GET['kelas_id'] ?? '';
        $selected_mapel = $_GET['mata_pelajaran_id'] ?? '';
        $selected_ta = $_GET['tahun_ajaran_id'] ?? '';
        
        if ($selected_kelas && $selected_mapel && $selected_ta) {
            $stmt = $this->pdo->prepare("
                SELECT * FROM penilaian_master 
                WHERE kelas_id = ? AND mata_pelajaran_id = ? AND tahun_ajaran_id = ?
                ORDER BY tanggal DESC
            ");
            $stmt->execute([$selected_kelas, $selected_mapel, $selected_ta]);
            $penilaian_list = $stmt->fetchAll();
        }
        
        $this->view('guru/penilaian_master', [
            'kelas' => $kelas,
            'mapel' => $mapel,
            'ta' => $ta,
            'penilaian_list' => $penilaian_list,
            'selected_kelas' => $selected_kelas,
            'selected_mapel' => $selected_mapel,
            'selected_ta' => $selected_ta,
            'title' => 'Manajemen Penilaian (Kurikulum Merdeka)'
        ]);
    }

    public function storePenilaian() {
        $kelas_id = $_POST['kelas_id'] ?? '';
        $mata_pelajaran_id = $_POST['mata_pelajaran_id'] ?? '';
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'] ?? '';
        $nama = $_POST['nama'] ?? '';
        $jenis_evaluasi = $_POST['jenis_evaluasi'] ?? '';
        $kategori = $_POST['kategori'] ?? '';
        $tipe = $_POST['tipe'] ?? '';
        $tanggal = $_POST['tanggal'] ?? '';
        
        if (empty($kelas_id) || empty($mata_pelajaran_id) || empty($tahun_ajaran_id) || empty($nama)) {
            $_SESSION['error'] = "Data tidak lengkap.";
            $this->redirect('/app/guru/penilaian');
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO penilaian_master (mata_pelajaran_id, kelas_id, tahun_ajaran_id, nama, jenis_evaluasi, kategori, tipe, tanggal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$mata_pelajaran_id, $kelas_id, $tahun_ajaran_id, $nama, $jenis_evaluasi, $kategori, $tipe, $tanggal]);
            $_SESSION['success'] = "Penilaian baru berhasil ditambahkan.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambah penilaian: " . $e->getMessage();
        }
        
        $this->redirect("/app/guru/penilaian?kelas_id=$kelas_id&mata_pelajaran_id=$mata_pelajaran_id&tahun_ajaran_id=$tahun_ajaran_id");
    }

    public function deletePenilaian() {
        $id = $_GET['id'] ?? '';
        $kelas_id = $_GET['kelas_id'] ?? '';
        $mapel_id = $_GET['mapel_id'] ?? '';
        $ta_id = $_GET['ta_id'] ?? '';
        
        if ($id) {
            $stmt = $this->pdo->prepare("DELETE FROM penilaian_master WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Penilaian berhasil dihapus.";
        }
        $this->redirect("/app/guru/penilaian?kelas_id=$kelas_id&mata_pelajaran_id=$mapel_id&tahun_ajaran_id=$ta_id");
    }

    public function rubrik() {
        $penilaian_id = $_GET['id'] ?? '';
        if (!$penilaian_id) {
            $this->redirect('/app/guru/penilaian');
            return;
        }

        $stmtMaster = $this->pdo->prepare("SELECT * FROM penilaian_master WHERE id = ?");
        $stmtMaster->execute([$penilaian_id]);
        $penilaian = $stmtMaster->fetch();

        if (!$penilaian) {
            $this->redirect('/app/guru/penilaian');
            return;
        }

        $stmtRubrik = $this->pdo->prepare("SELECT * FROM rubrik_aspek WHERE penilaian_master_id = ? ORDER BY id");
        $stmtRubrik->execute([$penilaian_id]);
        $rubrik_list = $stmtRubrik->fetchAll();

        $this->view('guru/penilaian_rubrik', [
            'penilaian' => $penilaian,
            'rubrik_list' => $rubrik_list,
            'title' => 'Atur Rubrik: ' . $penilaian['nama']
        ]);
    }

    public function storeRubrik() {
        $penilaian_id = $_POST['penilaian_master_id'] ?? '';
        $nama_aspek = $_POST['nama_aspek'] ?? '';
        $skala_maksimal = (int)($_POST['skala_maksimal'] ?? 4);
        $bobot_persen = (int)($_POST['bobot_persen'] ?? 100);

        if (!$penilaian_id || !$nama_aspek) {
            $this->redirect('/app/guru/penilaian');
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO rubrik_aspek (penilaian_master_id, nama_aspek, skala_maksimal, bobot_persen) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$penilaian_id, $nama_aspek, $skala_maksimal, $bobot_persen]);
            $_SESSION['success'] = "Aspek rubrik berhasil ditambahkan.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambah rubrik: " . $e->getMessage();
        }

        $this->redirect("/app/guru/penilaian/rubrik?id=$penilaian_id");
    }

    public function deleteRubrik() {
        $id = $_GET['id'] ?? '';
        $penilaian_id = $_GET['penilaian_id'] ?? '';

        if ($id) {
            $stmt = $this->pdo->prepare("DELETE FROM rubrik_aspek WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Aspek rubrik dihapus.";
        }
        $this->redirect("/app/guru/penilaian/rubrik?id=$penilaian_id");
    }

    public function inputNilai() {
        $penilaian_id = $_GET['id'] ?? '';
        if (!$penilaian_id) {
            $this->redirect('/app/guru/penilaian');
            return;
        }
        
        $stmtMaster = $this->pdo->prepare("
            SELECT p.*, k.nama_kelas, m.nama as nama_mapel, t.nama as nama_ta 
            FROM penilaian_master p
            JOIN kelas k ON p.kelas_id = k.id
            JOIN mata_pelajaran m ON p.mata_pelajaran_id = m.id
            JOIN tahun_ajaran t ON p.tahun_ajaran_id = t.id
            WHERE p.id = ?
        ");
        $stmtMaster->execute([$penilaian_id]);
        $penilaian = $stmtMaster->fetch();
        
        if (!$penilaian) {
            $this->redirect('/app/guru/penilaian');
            return;
        }

        // Fetch rubrik
        $stmtRubrik = $this->pdo->prepare("SELECT * FROM rubrik_aspek WHERE penilaian_master_id = ? ORDER BY id");
        $stmtRubrik->execute([$penilaian_id]);
        $rubrik_list = $stmtRubrik->fetchAll();
        $is_rubrik = count($rubrik_list) > 0;
        
        // Fetch siswa & nilai akhir
        $stmtSiswa = $this->pdo->prepare("
            SELECT s.id, s.nisn, s.nama, n.id as nilai_id, n.nilai_akhir, n.catatan_guru, n.file_upload
            FROM siswa s
            LEFT JOIN penilaian_nilai n ON s.id = n.siswa_id AND n.penilaian_master_id = ?
            WHERE s.kelas_id = ?
            ORDER BY s.nama
        ");
        $stmtSiswa->execute([$penilaian_id, $penilaian['kelas_id']]);
        $siswa = $stmtSiswa->fetchAll();

        // Fetch rubric details if exists
        $rubrik_detail = [];
        if ($is_rubrik) {
            $stmtDetail = $this->pdo->prepare("
                SELECT rd.rubrik_aspek_id, rd.skor, n.siswa_id
                FROM penilaian_rubrik_detail rd
                JOIN penilaian_nilai n ON rd.penilaian_nilai_id = n.id
                WHERE n.penilaian_master_id = ?
            ");
            $stmtDetail->execute([$penilaian_id]);
            $rawDetails = $stmtDetail->fetchAll();
            foreach ($rawDetails as $d) {
                $rubrik_detail[$d['siswa_id']][$d['rubrik_aspek_id']] = $d['skor'];
            }
        }
        
        $this->view('guru/penilaian_input', [
            'penilaian' => $penilaian,
            'siswa' => $siswa,
            'rubrik_list' => $rubrik_list,
            'is_rubrik' => $is_rubrik,
            'rubrik_detail' => $rubrik_detail,
            'title' => 'Input Nilai: ' . $penilaian['nama']
        ]);
    }

    public function storeInputNilai() {
        $penilaian_id = $_POST['penilaian_id'] ?? '';
        $nilai = $_POST['nilai'] ?? []; // direct nilai
        $catatan = $_POST['catatan'] ?? [];
        $rubrik_skor = $_POST['rubrik_skor'] ?? []; // [siswa_id][aspek_id] => skor
        $files = $_FILES['file_upload'] ?? null;
        
        if (!$penilaian_id) {
            $this->redirect('/app/guru/penilaian');
            return;
        }

        // Fetch rubrik structure to calculate nilai akhir
        $stmtRubrik = $this->pdo->prepare("SELECT * FROM rubrik_aspek WHERE penilaian_master_id = ?");
        $stmtRubrik->execute([$penilaian_id]);
        $rubrik_list = $stmtRubrik->fetchAll();
        $is_rubrik = count($rubrik_list) > 0;
        
        $upload_dir = 'uploads/proyek/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        try {
            $this->pdo->beginTransaction();
            
            // Get all students in this assessment
            $stmtSiswa = $this->pdo->prepare("SELECT s.id FROM siswa s JOIN penilaian_master p ON s.kelas_id = p.kelas_id WHERE p.id = ?");
            $stmtSiswa->execute([$penilaian_id]);
            $siswaList = $stmtSiswa->fetchAll();

            $stmtUpsertNilai = $this->pdo->prepare("
                INSERT INTO penilaian_nilai (penilaian_master_id, siswa_id, nilai_akhir, catatan_guru, file_upload) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE nilai_akhir = VALUES(nilai_akhir), catatan_guru = VALUES(catatan_guru), file_upload = COALESCE(VALUES(file_upload), file_upload)
            ");
            
            $stmtUpsertRubrik = $this->pdo->prepare("
                INSERT INTO penilaian_rubrik_detail (penilaian_nilai_id, rubrik_aspek_id, skor)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE skor = VALUES(skor)
            ");
            
            $stmtGetNilaiId = $this->pdo->prepare("SELECT id FROM penilaian_nilai WHERE penilaian_master_id = ? AND siswa_id = ?");

            foreach ($siswaList as $s) {
                $siswa_id = $s['id'];
                $cat = $catatan[$siswa_id] ?? '';
                $nilai_akhir = 0;
                
                if ($is_rubrik) {
                    // Hitung nilai akhir otomatis
                    $total_bobot = 0;
                    $akumulasi = 0;
                    foreach ($rubrik_list as $r) {
                        $skor = isset($rubrik_skor[$siswa_id][$r['id']]) ? (float)$rubrik_skor[$siswa_id][$r['id']] : 0;
                        $maks = $r['skala_maksimal'];
                        $bobot = $r['bobot_persen'];
                        $akumulasi += ($skor / $maks) * $bobot;
                        $total_bobot += $bobot;
                    }
                    if ($total_bobot > 0) {
                        $nilai_akhir = ($akumulasi / $total_bobot) * 100;
                    }
                } else {
                    $nilai_akhir = isset($nilai[$siswa_id]) && $nilai[$siswa_id] !== '' ? (float)$nilai[$siswa_id] : 0;
                }

                // Handle file upload
                $file_path = null;
                if (isset($files['name'][$siswa_id]) && $files['error'][$siswa_id] == UPLOAD_ERR_OK) {
                    $tmp_name = $files['tmp_name'][$siswa_id];
                    $name = time() . '_' . basename($files['name'][$siswa_id]);
                    $target_file = $upload_dir . $name;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $file_path = $target_file;
                    }
                }

                // First insert/update the main score
                $stmtUpsertNilai->execute([$penilaian_id, $siswa_id, $nilai_akhir, $cat, $file_path]);

                // If rubric, we need the nilai_id to insert details
                if ($is_rubrik) {
                    $stmtGetNilaiId->execute([$penilaian_id, $siswa_id]);
                    $nilai_row = $stmtGetNilaiId->fetch();
                    $nilai_id = $nilai_row['id'];
                    
                    foreach ($rubrik_list as $r) {
                        $skor = isset($rubrik_skor[$siswa_id][$r['id']]) ? (float)$rubrik_skor[$siswa_id][$r['id']] : 0;
                        $stmtUpsertRubrik->execute([$nilai_id, $r['id'], $skor]);
                    }
                }
            }
            
            $this->pdo->commit();
            $_SESSION['success'] = "Nilai berhasil disimpan.";
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $_SESSION['error'] = "Gagal menyimpan nilai: " . $e->getMessage();
        }
        
        $this->redirect("/app/guru/penilaian/input?id=$penilaian_id");
    }

    // --- END PENILAIAN KURIKULUM MERDEKA ---

    public function profileSettings() {
        $stmt = $this->pdo->query("SELECT * FROM web_profile WHERE id = 1");
        $profile = $stmt->fetch();
        
        // If somehow deleted, insert default
        if (!$profile) {
            $this->pdo->exec("INSERT INTO web_profile (id, hero_title, hero_subtitle) VALUES (1, 'Title', 'Subtitle')");
            $stmt = $this->pdo->query("SELECT * FROM web_profile WHERE id = 1");
            $profile = $stmt->fetch();
        }

        $this->view('guru/profile_settings', [
            'profile' => $profile,
            'title' => 'Web Profile Settings'
        ]);
    }

    public function storeProfileSettings() {
        $hero_title = $_POST['hero_title'] ?? '';
        $hero_subtitle = $_POST['hero_subtitle'] ?? '';
        $about_text = $_POST['about_text'] ?? '';
        $contact_email = $_POST['contact_email'] ?? '';
        $contact_phone = $_POST['contact_phone'] ?? '';
        $address = $_POST['address'] ?? '';

        try {
            $stmt = $this->pdo->prepare("
                UPDATE web_profile SET 
                hero_title = ?, hero_subtitle = ?, about_text = ?, 
                contact_email = ?, contact_phone = ?, address = ?
                WHERE id = 1
            ");
            $stmt->execute([$hero_title, $hero_subtitle, $about_text, $contact_email, $contact_phone, $address]);
            $_SESSION['success'] = "Pengaturan Web Profile berhasil disimpan.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menyimpan pengaturan: " . $e->getMessage();
        }

        $this->redirect('/app/guru/profile-settings');
    }
}

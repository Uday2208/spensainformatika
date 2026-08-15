<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webpresensi');

try {
    // 1. Connect without DB name to create DB
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    echo "Database created successfully.\n";
    
    // 2. Connect to the created DB
    $pdo->exec("USE `" . DB_NAME . "`");

    // 3. Create tables

    // Table: kelas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS kelas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_kelas VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'kelas' created.\n";

    // Table: siswa
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS siswa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nisn VARCHAR(20) UNIQUE NOT NULL,
            nama VARCHAR(100) NOT NULL,
            kelas_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
            INDEX (kelas_id)
        )
    ");
    echo "Table 'siswa' created.\n";

    // Table: users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('guru', 'siswa') NOT NULL DEFAULT 'siswa',
            siswa_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'users' created.\n";

    // Table: absensi
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS absensi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            kelas_id INT NOT NULL,
            tanggal DATE NOT NULL,
            pertemuan INT NOT NULL,
            status ENUM('hadir', 'sakit', 'izin', 'alpha') DEFAULT 'hadir',
            nilai_keaktifan INT DEFAULT 0,
            keterangan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_absensi (siswa_id, tanggal, pertemuan),
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
            FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
            INDEX (siswa_id),
            INDEX (kelas_id)
        )
    ");
    echo "Table 'absensi' created.\n";

    // Table: nilai
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS nilai (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            kelas_id INT NOT NULL,
            bab_modul VARCHAR(100) NOT NULL,
            jenis_nilai ENUM('tugas', 'quiz', 'projek') NOT NULL,
            nilai DECIMAL(5,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
            FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'nilai' created.\n";

    // Table: remedial
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS remedial (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            bab_modul VARCHAR(100) NOT NULL,
            nilai_remedial DECIMAL(5,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'remedial' created.\n";

    // Table: tahun_ajaran
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tahun_ajaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(50) NOT NULL,
            semester ENUM('Ganjil', 'Genap') NOT NULL,
            is_active TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'tahun_ajaran' created.\n";

    // Table: mata_pelajaran
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mata_pelajaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode VARCHAR(20) NOT NULL UNIQUE,
            nama VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'mata_pelajaran' created.\n";

    // Table: penilaian_master
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS penilaian_master (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mata_pelajaran_id INT NOT NULL,
            kelas_id INT NOT NULL,
            tahun_ajaran_id INT NOT NULL,
            nama VARCHAR(150) NOT NULL,
            jenis_evaluasi ENUM('Formatif', 'Sumatif') NOT NULL,
            kategori ENUM('Pengetahuan', 'Keterampilan') NOT NULL,
            tipe VARCHAR(50) NOT NULL,
            tanggal DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (mata_pelajaran_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE,
            FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
            FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'penilaian_master' created.\n";

    // Table: rubrik_aspek
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rubrik_aspek (
            id INT AUTO_INCREMENT PRIMARY KEY,
            penilaian_master_id INT NOT NULL,
            nama_aspek VARCHAR(150) NOT NULL,
            skala_maksimal INT NOT NULL DEFAULT 4,
            bobot_persen INT NOT NULL DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (penilaian_master_id) REFERENCES penilaian_master(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'rubrik_aspek' created.\n";

    // Table: penilaian_nilai
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS penilaian_nilai (
            id INT AUTO_INCREMENT PRIMARY KEY,
            penilaian_master_id INT NOT NULL,
            siswa_id INT NOT NULL,
            nilai_akhir DECIMAL(5,2) DEFAULT 0,
            catatan_guru TEXT,
            file_upload VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_penilaian_siswa (penilaian_master_id, siswa_id),
            FOREIGN KEY (penilaian_master_id) REFERENCES penilaian_master(id) ON DELETE CASCADE,
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'penilaian_nilai' created.\n";

    // Table: penilaian_rubrik_detail
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS penilaian_rubrik_detail (
            id INT AUTO_INCREMENT PRIMARY KEY,
            penilaian_nilai_id INT NOT NULL,
            rubrik_aspek_id INT NOT NULL,
            skor INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (penilaian_nilai_id) REFERENCES penilaian_nilai(id) ON DELETE CASCADE,
            FOREIGN KEY (rubrik_aspek_id) REFERENCES rubrik_aspek(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'penilaian_rubrik_detail' created.\n";

    // Table: web_profile
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS web_profile (
            id INT PRIMARY KEY DEFAULT 1,
            hero_title VARCHAR(255) NOT NULL,
            hero_subtitle TEXT NOT NULL,
            about_text TEXT,
            contact_email VARCHAR(100),
            contact_phone VARCHAR(50),
            address TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insert default web profile data
    $stmtProfile = $pdo->prepare("INSERT IGNORE INTO web_profile (id, hero_title, hero_subtitle, about_text, contact_email, contact_phone, address) VALUES (1, 'Sistem Akademik Informatika', 'Manajemen presensi dan e-Rapor yang cepat, aman, dan responsif.', 'Kami adalah institusi pendidikan terdepan yang berfokus pada teknologi dan informatika. Sistem akademik ini dibangun untuk memudahkan proses belajar mengajar.', 'info@sekolah.com', '+62 812 3456 7890', 'Jl. Pendidikan No. 1, Jakarta')");
    $stmtProfile->execute();
    echo "Table 'web_profile' created and seeded.\n";

    // Insert Default Guru User (password: guru123)
    $hashedPassword = password_hash('guru123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES ('guru', :password, 'guru')");
    $stmt->execute(['password' => $hashedPassword]);
    echo "Default Guru account created (username: guru, password: guru123).\n";

    echo "Initialization completed!\n";

} catch (PDOException $e) {
    die("DB Init failed: " . $e->getMessage() . "\n");
}

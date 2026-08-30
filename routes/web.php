<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UjianController;
use App\Http\Controllers\UjianSiswaController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AiKoreksiController;

// Public Routes
Route::get('/', [PublicController::class, 'index']);
Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{slug}', [ArtikelController::class, 'show'])->name('artikel.show');
Route::redirect('/blog', '/artikel', 301);

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// App Routes (Must be logged in)
Route::prefix('app')->middleware('auth')->group(function () {
    
    // Universal App Routes
    Route::post('/avatar', [AuthController::class, 'updateAvatar']);

    // ============ ADMIN ROUTES ============
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard-admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Manajemen Guru
        Route::get('/admin/data-guru', [AdminController::class, 'dataGuru'])->name('admin.guru.index');
        Route::post('/admin/data-guru', [AdminController::class, 'storeGuru'])->name('admin.guru.store');
        Route::put('/admin/data-guru/{id}', [AdminController::class, 'updateGuru'])->name('admin.guru.update');
        Route::delete('/admin/data-guru/{id}', [AdminController::class, 'destroyGuru'])->name('admin.guru.destroy');
        Route::post('/admin/data-guru/{id}/reset-password', [AdminController::class, 'resetPasswordGuru'])->name('admin.guru.reset-password');
        Route::post('/admin/guru/{id}/pengampu', [AdminController::class, 'updateGuruPengampu'])->name('admin.guru.pengampu');

        // Manajemen Kelas
        Route::get('/admin/data-kelas', [AdminController::class, 'dataKelas'])->name('admin.kelas.index');
        Route::post('/admin/data-kelas', [AdminController::class, 'storeKelas'])->name('admin.kelas.store');
        Route::delete('/admin/data-kelas/{id}', [AdminController::class, 'destroyKelas'])->name('admin.kelas.destroy');

        // Manajemen Siswa
        Route::get('/admin/data-siswa', [AdminController::class, 'dataSiswa'])->name('admin.siswa.index');
        Route::post('/admin/data-siswa', [AdminController::class, 'storeSiswa'])->name('admin.siswa.store');
        Route::put('/admin/data-siswa/{id}', [AdminController::class, 'updateSiswa'])->name('admin.siswa.update');
        Route::delete('/admin/data-siswa/{id}', [AdminController::class, 'destroySiswa'])->name('admin.siswa.destroy');
        Route::post('/admin/data-siswa/{id}/reset-password', [AdminController::class, 'resetPasswordSiswa'])->name('admin.siswa.reset-password');
        Route::post('/admin/import-siswa', [AdminController::class, 'importSiswa'])->name('admin.siswa.import');
        Route::get('/admin/export-siswa', [AdminController::class, 'exportSiswa'])->name('admin.siswa.export');

        // Moderasi Komentar
        Route::get('/admin/kelola-komentar', [AdminController::class, 'kelolaKomentar'])->name('admin.komentar.index');
        Route::delete('/admin/komentar/{id}', [AdminController::class, 'destroyKomentar'])->name('admin.komentar.destroy');

        // Artikel Web
        Route::get('/admin/artikel', [AdminController::class, 'artikel'])->name('admin.artikel.index');
        Route::post('/admin/artikel', [AdminController::class, 'storeArtikel'])->name('admin.artikel.store');
        Route::put('/admin/artikel/{id}', [AdminController::class, 'updateArtikel'])->name('admin.artikel.update');
        Route::delete('/admin/artikel/{id}', [AdminController::class, 'destroyArtikel'])->name('admin.artikel.destroy');

        // Pengaturan
        Route::get('/admin/pengaturan', [AdminController::class, 'pengaturan'])->name('admin.pengaturan');
        Route::post('/admin/pengaturan', [AdminController::class, 'storePengaturan'])->name('admin.pengaturan.store');
        Route::post('/admin/setting-kkm', [AdminController::class, 'updateSettingKkm'])->name('admin.setting-kkm');
        Route::post('/admin/setting-komentar', [AdminController::class, 'updateSettingKomentar'])->name('admin.setting-komentar');
    });

    // Guru Routes
    Route::middleware('role:guru')->group(function () {
        Route::get('/dashboard-guru', [GuruController::class, 'dashboard']);

        // ============ TUGAS SISWA (GURU) ============
        Route::get('/tugas', [GuruController::class, 'tugas'])->name('guru.tugas.index');
        Route::post('/tugas', [GuruController::class, 'storeTugas'])->name('guru.tugas.store');
        Route::delete('/tugas/{id}', [GuruController::class, 'destroyTugas'])->name('guru.tugas.destroy');

        Route::get('/absensi', [GuruController::class, 'absensi']);
        Route::post('/absensi', [GuruController::class, 'storeAbsensi']);
        Route::delete('/absensi/delete-by-date', [GuruController::class, 'destroyAbsensiByDate']);
        Route::get('/penilaian-harian', [GuruController::class, 'penilaianHarian']);
        Route::post('/penilaian-harian', [GuruController::class, 'storePenilaianHarian']);
        Route::put('/penilaian-harian/{id}', [GuruController::class, 'updatePenilaianHarianSingle'])->name('guru.penilaian-harian.update-single');
        Route::delete('/penilaian-harian/{id}', [GuruController::class, 'destroyPenilaianHarianSingle'])->name('guru.penilaian-harian.destroy-single');
        Route::get('/rekap-keaktifan', [GuruController::class, 'rekapKeaktifan'])->name('guru.rekap-keaktifan');
        Route::get('/rekap-keaktifan/export', [GuruController::class, 'exportRekapKeaktifan'])->name('guru.rekap-keaktifan.export');
        Route::get('/nilai', [GuruController::class, 'nilai']);
        Route::get('/nilai/export', [GuruController::class, 'exportNilai'])->name('guru.nilai.export');
        Route::get('/nilai/rata-ujian', [GuruController::class, 'getRataUjian'])->name('guru.nilai.rata-ujian');
        Route::post('/nilai', [GuruController::class, 'storeNilai']);
        Route::put('/nilai/{id}', [GuruController::class, 'updateNilai']);
        Route::delete('/nilai/{id}', [GuruController::class, 'destroyNilai']);
        Route::delete('/nilai-by-bab', [GuruController::class, 'destroyNilaiByBab'])->name('guru.nilai.destroy-by-bab');
        Route::get('/rekap-absensi', [GuruController::class, 'rekapAbsensi']);
        Route::get('/rekap-absensi/export', [GuruController::class, 'exportRekapAbsensi']);
        Route::get('/rekap-absensi/siswa/{id}', [GuruController::class, 'rekapAbsensiSiswa']);
        Route::get('/rekap-jurnal', [GuruController::class, 'rekapJurnal']);
        Route::get('/jurnal/get-template', [GuruController::class, 'getJurnalTemplate'])->name('guru.jurnal.get-template');
        Route::post('/jurnal', [GuruController::class, 'storeJurnal'])->name('guru.jurnal.store');
        Route::delete('/jurnal/{id}', [GuruController::class, 'destroyJurnal'])->name('guru.jurnal.destroy');
        Route::get('/rekap-jurnal/export', [GuruController::class, 'exportRekapJurnal'])->name('guru.rekap-jurnal.export');

        // ============ UJIAN HARIAN (GURU) ============
        Route::get('/ujian', [UjianController::class, 'index'])->name('guru.ujian.index');
        Route::get('/ujian/create', [UjianController::class, 'create'])->name('guru.ujian.create');
        Route::post('/ujian', [UjianController::class, 'store'])->name('guru.ujian.store');
        Route::get('/ujian/{id}', [UjianController::class, 'show'])->name('guru.ujian.show');
        Route::put('/ujian/{id}', [UjianController::class, 'update'])->name('guru.ujian.update');
        Route::delete('/ujian/{id}', [UjianController::class, 'destroy'])->name('guru.ujian.destroy');
        Route::post('/ujian/{id}/soal', [UjianController::class, 'storeSoal'])->name('guru.ujian.soal.store');
        Route::put('/ujian/{id}/soal/{soalId}', [UjianController::class, 'updateSoal'])->name('guru.ujian.soal.update');
        Route::delete('/ujian/{id}/soal/{soalId}', [UjianController::class, 'destroySoal'])->name('guru.ujian.soal.destroy');
        Route::post('/ujian/{id}/activate', [UjianController::class, 'activate'])->name('guru.ujian.activate');
        Route::post('/ujian/{id}/setting', [UjianController::class, 'saveSetting'])->name('guru.ujian.setting');
        Route::post('/ujian/{id}/finish', [UjianController::class, 'finish'])->name('guru.ujian.finish');
        Route::get('/ujian/{id}/monitoring', [UjianController::class, 'monitoring'])->name('guru.ujian.monitoring');
        Route::get('/ujian/{id}/koreksi', [UjianController::class, 'koreksiEssay'])->name('guru.ujian.koreksi');
        Route::post('/ujian/{id}/koreksi', [UjianController::class, 'storeKoreksi'])->name('guru.ujian.koreksi.store');
        Route::post('/ujian/{id}/finalisasi', [UjianController::class, 'finalisasi'])->name('guru.ujian.finalisasi');

        // ============ HASIL & KOREKSI UJIAN (GURU) ============
        Route::get('/hasil-ujian', [UjianController::class, 'indexHasil'])->name('guru.hasil.index');
        Route::get('/hasil-ujian/{id}', [UjianController::class, 'showHasil'])->name('guru.hasil.show');
        Route::get('/hasil-ujian/{id}/siswa/{siswaId}', [UjianController::class, 'detailJawabanSiswa'])->name('guru.hasil.detail-siswa');
        Route::post('/hasil-ujian/{id}/siswa/{siswaId}/nilai', [UjianController::class, 'updateNilaiSiswaIndividu'])->name('guru.hasil.update-siswa');

        // ============ AI KOREKSI ESSAY (GURU) ============
        Route::post('/ujian/jawaban/{jawabanId}/koreksi-ai', [AiKoreksiController::class, 'gradeSingle'])->middleware('throttle:30,1')->name('guru.ujian.koreksi-ai');
        Route::post('/ujian/koreksi-ai/{id}/accept', [AiKoreksiController::class, 'acceptScore'])->name('guru.ujian.koreksi-ai.accept');
    });

    // Siswa Routes
    Route::middleware('role:siswa')->group(function () {
        Route::get('/dashboard-siswa', [SiswaController::class, 'dashboard'])->name('siswa.dashboard');
        Route::get('/kehadiran-saya', [SiswaController::class, 'kehadiranSaya'])->name('siswa.kehadiran');
        Route::get('/tugas-siswa', [SiswaController::class, 'tugas'])->name('siswa.tugas');
        Route::get('/materi-siswa', [SiswaController::class, 'tugas'])->name('siswa.materi');
        Route::get('/kesan-masukan', [SiswaController::class, 'kesanMasukan'])->name('siswa.kesan-masukan');
        Route::get('/profil-saya', [SiswaController::class, 'profilSaya'])->name('siswa.profil');
        Route::put('/profil', [SiswaController::class, 'updateProfil'])->name('siswa.profil.update');
        Route::post('/komentar', [SiswaController::class, 'storeKomentar'])->name('siswa.komentar.store');

        // ============ UJIAN HARIAN (SISWA) ============
        Route::get('/ujian-siswa', [UjianSiswaController::class, 'index'])->name('siswa.ujian.index');
        Route::post('/ujian-siswa/{id}/masuk', [UjianSiswaController::class, 'masuk'])->name('siswa.ujian.masuk');
        Route::get('/ujian-siswa/{id}/kerjakan', [UjianSiswaController::class, 'kerjakan'])->name('siswa.ujian.kerjakan');
        Route::post('/ujian-siswa/{id}/simpan-jawaban', [UjianSiswaController::class, 'simpanJawaban'])->name('siswa.ujian.simpan');
        Route::post('/ujian-siswa/{id}/submit', [UjianSiswaController::class, 'submit'])->name('siswa.ujian.submit');
        Route::get('/ujian-siswa/{id}/hasil', [UjianSiswaController::class, 'hasil'])->name('siswa.ujian.hasil');
        Route::post('/ujian-siswa/log-kecurangan', [UjianSiswaController::class, 'logKecurangan'])->name('siswa.ujian.log');
    });
});

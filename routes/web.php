<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UjianController;
use App\Http\Controllers\UjianSiswaController;
use App\Http\Controllers\ArtikelController;

// Public Routes
Route::get('/', [PublicController::class, 'index']);
Route::get('/about', [PublicController::class, 'about']);
Route::get('/portfolio', [PublicController::class, 'portfolio']);
Route::get('/blog', [PublicController::class, 'blog']);
Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{slug}', [ArtikelController::class, 'show'])->name('artikel.show');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// App Routes (Must be logged in)
Route::prefix('app')->middleware('auth')->group(function () {
    
    // Universal App Routes
    Route::post('/avatar', [AuthController::class, 'updateAvatar']);

    // Guru Routes
    Route::middleware('role:guru')->group(function () {
        Route::get('/dashboard-guru', [GuruController::class, 'dashboard']);
        Route::get('/artikel', [GuruController::class, 'artikel']);
        Route::post('/artikel', [GuruController::class, 'storeArtikel']);
        Route::put('/artikel/{id}', [GuruController::class, 'updateArtikel']);
        Route::delete('/artikel/{id}', [GuruController::class, 'destroyArtikel']);
        Route::get('/materi', [GuruController::class, 'materi']);
        Route::post('/materi', [GuruController::class, 'storeMateri']);
        Route::delete('/materi/{id}', [GuruController::class, 'destroyMateri']);
        Route::get('/data-kelas', [GuruController::class, 'dataKelas']);
        Route::post('/data-kelas', [GuruController::class, 'storeKelas']);
        Route::delete('/data-kelas/{id}', [GuruController::class, 'destroyKelas']);
        Route::get('/data-siswa', [GuruController::class, 'dataSiswa']);
        Route::post('/data-siswa', [GuruController::class, 'storeSiswa']);
        Route::put('/data-siswa/{id}', [GuruController::class, 'updateSiswa']);
        Route::delete('/data-siswa/{id}', [GuruController::class, 'destroySiswa']);
        Route::post('/data-siswa/{id}/reset-password', [GuruController::class, 'resetPasswordSiswa']);
        Route::post('/import-siswa', [GuruController::class, 'importSiswa']);
        Route::get('/export-siswa', [GuruController::class, 'exportSiswa']);
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
        Route::get('/kelola-komentar', [GuruController::class, 'kelolaKomentar']);
        Route::delete('/komentar/{id}', [GuruController::class, 'destroyKomentar']);
        Route::post('/setting-komentar', [GuruController::class, 'updateSettingKomentar']);
        Route::post('/setting-kkm', [GuruController::class, 'updateSettingKkm']);
        Route::get('/rekap-absensi', [GuruController::class, 'rekapAbsensi']);
        Route::get('/rekap-absensi/export', [GuruController::class, 'exportRekapAbsensi']);
        Route::get('/rekap-absensi/siswa/{id}', [GuruController::class, 'rekapAbsensiSiswa']);
        Route::get('/rekap-jurnal', [GuruController::class, 'rekapJurnal']);
        Route::get('/jurnal/get-template', [GuruController::class, 'getJurnalTemplate'])->name('guru.jurnal.get-template');
        Route::post('/jurnal', [GuruController::class, 'storeJurnal'])->name('guru.jurnal.store');
        Route::delete('/jurnal/{id}', [GuruController::class, 'destroyJurnal'])->name('guru.jurnal.destroy');
        Route::get('/rekap-jurnal/export', [GuruController::class, 'exportRekapJurnal'])->name('guru.rekap-jurnal.export');
        Route::get('/pengaturan', [GuruController::class, 'pengaturan']);
        Route::post('/pengaturan', [GuruController::class, 'storePengaturan']);

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
    });

    // Siswa Routes
    Route::middleware('role:siswa')->group(function () {
        Route::get('/dashboard-siswa', [SiswaController::class, 'dashboard']);
        Route::get('/kehadiran-saya', [SiswaController::class, 'kehadiranSaya'])->name('siswa.kehadiran');
        Route::put('/profil', [SiswaController::class, 'updateProfil']);
        Route::post('/komentar', [SiswaController::class, 'storeKomentar']);

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

/**
 * Code.gs (Frontend Router)
 * Entry point untuk Google Apps Script Web App
 */

// Memasukkan file HTML lain dan memproses variabel di dalamnya
function include(filename) {
  const template = HtmlService.createTemplateFromFile(filename);
  template.scriptUrl = ScriptApp.getService().getUrl();
  return template.evaluate().getContent();
}

function doGet(e) {
  const page = e.parameter.page || 'home';
  const token = e.parameter.token || null;
  
  // Middleware Check (Dari RouterAuthMiddleware yang dibuat di Fase 4)
  const routeMap = {
    'home': '/',
    'login': '/login',
    'dashboard-guru': '/dashboard-guru',
    'dashboard-siswa': '/dashboard-siswa',
    'data-kelas': '/data-kelas',
    'data-siswa': '/data-siswa',
    'absensi': '/absensi',
    'penilaian-harian': '/penilaian-harian',
    'nilai': '/nilai',
    'materi': '/materi',
    'artikel': '/artikel',
    'ujian': '/ujian',
    'hasil-ujian': '/hasil-ujian',
    'rekap-absensi': '/rekap-absensi',
    'rekap-jurnal': '/rekap-jurnal',
    'kelola-komentar': '/kelola-komentar',
    'pengaturan': '/pengaturan',
    'ujian-siswa': '/ujian-siswa',
    'kehadiran-saya': '/kehadiran-saya'
  };
  
  const simulatedPath = routeMap[page] || '/404';
  const access = RouterAuthMiddleware.verifyPageAccess(simulatedPath, token);
  
  // Jika middleware me-return path string (bukan true), berarti harus dialihkan
  if (access !== true) {
    if (access === '/login') {
      return HtmlService.createHtmlOutput('<script>window.top.location.href="' + ScriptApp.getService().getUrl() + '?page=login";</script>');
    }
    // Jika 404 atau 403
    if (access === '/404' || access === '/403') {
      return HtmlService.createHtmlOutput(`<h1>Error: ${access}</h1>`);
    }
  }

  // Load Template Engine (Public vs Internal)
  let template;
  // Jika rute adalah home atau login, pakai PublicLayout
  if (page === 'home' || page === 'login') {
    template = HtmlService.createTemplateFromFile('PublicLayout');
  } else {
    // Selain itu berarti rute internal, pakai AppLayout
    template = HtmlService.createTemplateFromFile('AppLayout');
  }
  
  // Pass current page context to layout
  template.page = page;
  template.scriptUrl = ScriptApp.getService().getUrl();
  
  // Pass Data dari Database jika halaman Home
  if (page === 'home') {
    const stats = {
      siswa: Database.table('siswas').get().length,
      kelas: Database.table('kelas').get().length,
      materi: 0 // Placeholder
    };
    template.statsJSON = JSON.stringify(stats);
    
    // Ambil artikel (asumsikan kita punya tabel artikels, kita mock up jika kosong)
    let artikels = Database.table('artikels').get() || [];
    if (artikels.length === 0) {
      artikels = [
        { judul: 'Panduan Pembelajaran Daring', konten: 'Berikut adalah panduan lengkap untuk...', created_at: '2026-08-14' },
        { judul: 'Jadwal Ujian Semester Ganjil', konten: 'Ujian semester ganjil akan dilaksanakan...', created_at: '2026-08-12' },
        { judul: 'Lomba Desain Web Nasional', konten: 'Pendaftaran lomba desain web telah dibuka...', created_at: '2026-08-10' }
      ];
    }
    template.artikelsJSON = JSON.stringify(artikels.slice(0, 3));
    
    // Testimoni (Mock data jika kosong)
    const komentars = [
      { isi_komentar: "Aplikasi ini sangat membantu proses belajar!", is_anonim: false, user_name: "Budi", created_at: "2 hari lalu" },
      { isi_komentar: "Tampilannya sangat bagus dan mudah dipahami.", is_anonim: true, user_name: "Anonim", created_at: "1 minggu lalu" }
    ];
    template.komentarsJSON = JSON.stringify(komentars);
  }
  
  return template.evaluate()
    .setTitle('Guru Informatika | Personal & Akademik')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

/**
 * RPC Wrappers for Client-Side JS (google.script.run)
 */
function login(data) {
  return AuthService.login(data);
}

function getGuruDashboardStats(token) {
  return DashboardService.getGuruStats(token);
}

// ==========================================
// KELAS SERVICE RPC
// ==========================================

function getDataKelas(token) {
  return KelasService.getDataKelas(token);
}

function storeKelas(token, data) {
  return KelasService.storeKelas(token, data);
}

function deleteKelas(token, id) {
  return KelasService.deleteKelas(token, id);
}

// ==========================================
// GURU SISWA SERVICE RPC
// ==========================================

function getDataSiswa(token) {
  return GuruSiswaService.getDataSiswa(token);
}

function storeSiswa(token, data) {
  return GuruSiswaService.storeSiswa(token, data);
}

function updateSiswa(token, id, data) {
  return GuruSiswaService.updateSiswa(token, id, data);
}

function deleteSiswa(token, id) {
  return GuruSiswaService.destroySiswa(token, id);
}

function resetPasswordSiswa(token, id) {
  return GuruSiswaService.resetPasswordSiswa(token, id);
}

function importSiswaBulk(token, kelas_id, rows) {
  return GuruSiswaService.importSiswaBulk(token, kelas_id, rows);
}

function getExportSiswa(token, kelas_id) {
  return GuruSiswaService.getExportSiswa(token, kelas_id);
}

// ==========================================
// GURU ABSENSI SERVICE RPC
// ==========================================

function getAbsensiInit(token) {
  return GuruAbsensiService.getAbsensiInit(token);
}

function getAbsensi(token, kelas_id, tanggal) {
  return GuruAbsensiService.getAbsensi(token, kelas_id, tanggal);
}

function storeAbsensi(token, payload) {
  return GuruAbsensiService.storeAbsensi(token, payload);
}

function destroyAbsensiByDate(token, payload) {
  return GuruAbsensiService.destroyAbsensiByDate(token, payload);
}

// ==========================================
// GURU PENILAIAN SERVICE RPC
// ==========================================

function getPenilaianInit(token) {
  return GuruPenilaianService.getPenilaianInit(token);
}

function getPenilaianData(token, kelas_id, tanggal, pertemuan) {
  return GuruPenilaianService.getPenilaianData(token, kelas_id, tanggal, pertemuan);
}

function storePenilaian(token, payload) {
  return GuruPenilaianService.storePenilaian(token, payload);
}

// ==========================================
// GURU NILAI SERVICE RPC
// ==========================================

function getNilaiInit(token) {
  return GuruNilaiService.getNilaiInit(token);
}

function getNilaiData(token, kelas_id) {
  return GuruNilaiService.getNilaiData(token, kelas_id);
}

function getRataUjian(token, bab) {
  return GuruNilaiService.getRataUjian(token, bab);
}

function storeNilai(token, payload) {
  return GuruNilaiService.storeNilai(token, payload);
}

function updateNilai(token, id, payload) {
  return GuruNilaiService.updateNilai(token, id, payload);
}

function destroyNilai(token, id) {
  return GuruNilaiService.destroyNilai(token, id);
}

function destroyNilaiByBab(token, bab) {
  return GuruNilaiService.destroyNilaiByBab(token, bab);
}

function updateKkm(token, kkm) {
  return GuruNilaiService.updateKkm(token, kkm);
}

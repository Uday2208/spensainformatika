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
    let stats = { siswa: 0, kelas: 0, materi: 0 };
    let artikelsList = [];
    let komentarsList = [];

    try {
      stats.siswa = Database.table('siswas').get().length;
      stats.kelas = Database.table('kelas').get().length;
      stats.materi = Database.table('materis').get().length;
    } catch(e) {
      Logger.log("Error stats: " + e.message);
    }

    try {
      let rawArtikels = Database.table('artikels').get() || [];
      if (rawArtikels.length > 0) {
        // Sort DESC
        rawArtikels.sort(function(a, b) {
          var dateA = new Date(a.created_at || 0).getTime();
          var dateB = new Date(b.created_at || 0).getTime();
          if (dateB !== dateA) return dateB - dateA;
          return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
        });

        // Ambil 3 artikel teratas dan sanitasi ukurannya
        artikelsList = rawArtikels.slice(0, 3).map(function(item) {
          var coverImg = '';
          if (item.gambar) {
            if (Array.isArray(item.gambar)) {
              coverImg = item.gambar[0] || '';
            } else if (typeof item.gambar === 'string') {
              try {
                var parsed = JSON.parse(item.gambar);
                coverImg = Array.isArray(parsed) ? (parsed[0] || '') : item.gambar;
              } catch(e) {
                coverImg = item.gambar;
              }
            }
          }
          return {
            id: item.id || '',
            judul: item.judul || 'Tanpa Judul',
            slug: item.slug || ('artikel-' + item.id),
            konten: item.konten ? item.konten.toString().substring(0, 200) : '',
            gambar: coverImg ? [coverImg] : [],
            created_at: item.created_at || ''
          };
        });
      }
    } catch(e) {
      Logger.log("Error artikels: " + e.message);
    }

    if (artikelsList.length === 0) {
      artikelsList = [
        { id: 1, judul: 'Panduan Pembelajaran Daring', slug: 'panduan-pembelajaran-daring', konten: 'Berikut adalah panduan lengkap untuk...', gambar: [], created_at: '2026-08-14' },
        { id: 2, judul: 'Jadwal Ujian Semester Ganjil', slug: 'jadwal-ujian-semester-ganjil', konten: 'Ujian semester ganjil akan dilaksanakan...', gambar: [], created_at: '2026-08-12' },
        { id: 3, judul: 'Lomba Desain Web Nasional', slug: 'lomba-desain-web-nasional', konten: 'Pendaftaran lomba desain web telah dibuka...', gambar: [], created_at: '2026-08-10' }
      ];
    }

    try {
      let rawKomentars = Database.table('komentars').get() || [];
      if (rawKomentars.length > 0) {
        komentarsList = rawKomentars.slice(0, 10).map(function(k) {
          return {
            isi_komentar: k.komentar || k.isi_komentar || '',
            is_anonim: k.is_anonim === true || k.is_anonim === 'true' || k.is_anonim === 1 || k.is_anonim === '1',
            user_name: k.user_name || 'Siswa',
            created_at: k.created_at || ''
          };
        });
      }
    } catch(e) {}

    if (komentarsList.length === 0) {
      komentarsList = [
        { isi_komentar: "Aplikasi ini sangat membantu proses belajar!", is_anonim: false, user_name: "Budi", created_at: "2 hari lalu" },
        { isi_komentar: "Tampilannya sangat bagus dan mudah dipahami.", is_anonim: true, user_name: "Anonim", created_at: "1 minggu lalu" }
      ];
    }

    template.statsJSON = JSON.stringify(stats);
    template.artikelsJSON = JSON.stringify(artikelsList);
    template.komentarsJSON = JSON.stringify(komentarsList);
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

function getSiswaDashboard(token) {
  return DashboardService.getSiswaDashboard(token);
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

// ==========================================
// ARTIKEL SERVICE RPC (FASE ARTIKEL)
// ==========================================

function getArtikelInit(token) {
  return ArtikelService.getArtikelInit(token);
}

function getArtikelPublic(page, perPage, search) {
  return ArtikelService.getArtikelPublic(page, perPage, search);
}

function getArtikelDetail(slugOrId) {
  return ArtikelService.getArtikelDetail(slugOrId);
}

function storeArtikel(token, payload) {
  return ArtikelService.storeArtikel(token, payload);
}

function updateArtikel(token, id, payload) {
  return ArtikelService.updateArtikel(token, id, payload);
}

function destroyArtikel(token, id) {
  return ArtikelService.destroyArtikel(token, id);
}

// ==========================================
// UJIAN HARIAN & HASIL UJIAN SERVICE RPC
// ==========================================

function getUjianInit(token, statusFilter) {
  return UjianService.getUjianInit(token, statusFilter);
}

function getUjianDetail(token, id) {
  return UjianService.getUjianDetail(token, id);
}

function storeUjian(token, payload) {
  return UjianService.storeUjian(token, payload);
}

function saveSettingUjian(token, id, payload) {
  return UjianService.saveSettingUjian(token, id, payload);
}

function destroyUjian(token, id) {
  return UjianService.destroyUjian(token, id);
}

function storeSoal(token, ujianId, payload) {
  return UjianService.storeSoal(token, ujianId, payload);
}

function updateSoal(token, ujianId, soalId, payload) {
  return UjianService.updateSoal(token, ujianId, soalId, payload);
}

function destroySoal(token, ujianId, soalId) {
  return UjianService.destroySoal(token, ujianId, soalId);
}

function activateUjian(token, id) {
  return UjianService.activateUjian(token, id);
}

function finishUjian(token, id) {
  return UjianService.finishUjian(token, id);
}

function getMonitoringData(token, id) {
  return UjianService.getMonitoringData(token, id);
}

function getKoreksiData(token, id) {
  return UjianService.getKoreksiData(token, id);
}

function storeKoreksiEssay(token, id, payload) {
  return UjianService.storeKoreksiEssay(token, id, payload);
}

function finalisasiUjian(token, id) {
  return UjianService.finalisasiUjian(token, id);
}

function getHasilUjianList(token, statusFilter) {
  return UjianService.getHasilUjianList(token, statusFilter);
}

function getHasilUjianDetail(token, id, kelasId) {
  return UjianService.getHasilUjianDetail(token, id, kelasId);
}

function getDetailJawabanSiswa(token, id, siswaId) {
  return UjianService.getDetailJawabanSiswa(token, id, siswaId);
}

function updateNilaiSiswaIndividu(token, id, siswaId, payload) {
  return UjianService.updateNilaiSiswaIndividu(token, id, siswaId, payload);
}

// ==========================================
// SISWA SERVICE & CBT SISWA RPC
// ==========================================

function updateProfilSiswa(token, payload) {
  return SiswaService.updateProfil(token, payload);
}

function storeKomentarSiswa(token, payload) {
  return SiswaService.storeKomentar(token, payload);
}

function getKehadiranSaya(token) {
  return SiswaService.getKehadiranSaya(token);
}

function getUjianSiswaList(token) {
  return SiswaService.getUjianSiswaList(token);
}

function masukUjianSiswa(token, ujianId, tokenMasuk) {
  return UjianSiswaService.masukUjian(token, ujianId, tokenMasuk);
}

function getSoalUjianSiswa(token, ujianId) {
  return UjianSiswaService.getSoalUjian(token, ujianId);
}

function simpanJawabanSiswa(token, ujianId, jawabanPayload) {
  return UjianSiswaService.saveAnswer(token, ujianId, jawabanPayload);
}

function submitUjianSiswa(token, ujianId, jawabanPayload) {
  return UjianSiswaService.submitUjian(token, ujianId, jawabanPayload);
}

function logKecuranganUjian(token, ujianId, event, detail) {
  return UjianSiswaService.logKecurangan(token, ujianId, event, detail);
}


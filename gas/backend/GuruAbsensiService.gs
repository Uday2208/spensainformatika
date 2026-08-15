/**
 * GuruAbsensiService.gs
 * Migrasi dari GuruController (Logika Absensi)
 */

const GuruAbsensiService = {
  
  /**
   * Mengambil inisialisasi data untuk halaman Absensi (Daftar Kelas)
   */
  getAbsensiInit: function(token) {
    try {
      SessionManager.requireRole(token, 'guru');
      const kelas = Database.table('kelas').get();
      return ResponseFormat.success({ kelas: kelas });
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Mengambil daftar siswa dan absensi mereka berdasarkan kelas dan tanggal
   */
  getAbsensi: function(token, kelas_id, tanggal) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      if (!kelas_id || !tanggal) {
          return ResponseFormat.error("Kelas dan tanggal harus dipilih.");
      }
      
      // Ambil data siswa di kelas tersebut (eager load user + kelas)
      const siswas = Database.table('siswas').where('kelas_id', '=', kelas_id).get();
      const allUsers = Database.table('users').get();
      const allKelas = Database.table('kelas').get();
      
      let mappedSiswas = siswas.map(s => {
          const user = allUsers.find(u => u.id == s.user_id) || {};
          const kls = allKelas.find(k => k.id == s.kelas_id) || {};
          
          return {
              id: s.id,
              nis: s.nis,
              nama: user.name || s.nama,
              kelas_nama: kls.nama_kelas || '-'
          };
      });
      
      // Ambil data absensi untuk siswa-siswa ini pada tanggal tersebut
      const absensiHariIni = Database.table('absensis').where('tanggal', '=', tanggal).get();
      
      // Map existing absensi status ke masing-masing siswa
      let sudahDiisi = false;
      let finalData = mappedSiswas.map(siswa => {
          const ab = absensiHariIni.find(a => a.siswa_id == siswa.id);
          if (ab) {
              sudahDiisi = true;
              siswa.status = ab.status;
              siswa.adaData = true;
          } else {
              siswa.status = 'hadir'; // Default
              siswa.adaData = false;
          }
          return siswa;
      });
      
      return ResponseFormat.success({
          siswas: finalData,
          sudahDiisi: sudahDiisi
      });
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Menyimpan / Upsert Absensi
   */
  storeAbsensi: function(token, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const tanggal = payload.tanggal;
      const absensiArray = payload.absensi; // Format: [{siswa_id: 1, status: 'hadir'}, ...]
      
      if (!tanggal || !Array.isArray(absensiArray)) {
          return ResponseFormat.validationError({ general: ["Format data absensi tidak valid."] });
      }
      
      const now = new Date().toISOString();
      
      Database.transaction(() => {
          absensiArray.forEach(item => {
              // Cek apakah sudah ada (Upsert logic)
              const existing = Database.table('absensis')
                  .where('siswa_id', '=', item.siswa_id)
                  .where('tanggal', '=', tanggal)
                  .get();
                  
              if (existing.length > 0) {
                  // Update
                  Database.table('absensis').where('id', '=', existing[0].id).update({
                      status: item.status,
                      updated_at: now
                  });
              } else {
                  // Insert
                  Database.table('absensis').insert({
                      siswa_id: item.siswa_id,
                      tanggal: tanggal,
                      status: item.status,
                      created_at: now,
                      updated_at: now
                  });
              }
          });
          return true;
      });
      
      return ResponseFormat.success(null, 'Absensi berhasil disimpan!');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Menghapus absensi berdasarkan tanggal (Zona Bahaya)
   */
  destroyAbsensiByDate: function(token, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const tanggal = payload.tanggal_hapus;
      const kelas_id = payload.kelas_id_hapus;
      
      if (!tanggal || !kelas_id) {
          return ResponseFormat.validationError({ general: ["Tanggal dan Kelas harus diisi."] });
      }
      
      Database.transaction(() => {
          const tableAbsensi = Database.table('absensis');
          
          if (kelas_id === 'all') {
              tableAbsensi.where('tanggal', '=', tanggal).delete();
          } else {
              // Perlu hapus berdasarkan kelas_id.
              // Cari siswa-siswa yang ada di kelas_id ini
              const siswasInKelas = Database.table('siswas').where('kelas_id', '=', kelas_id).get();
              const siswaIds = siswasInKelas.map(s => s.id);
              
              // Karena GAS Database implementation saya mungkin tidak support whereIn yang kompleks di delete,
              // kita cari data absensi di tanggal tsb, lalu hapus jika siswa_id nya ada di siswaIds
              const allAbsenToday = tableAbsensi.where('tanggal', '=', tanggal).get();
              allAbsenToday.forEach(absen => {
                  if (siswaIds.includes(absen.siswa_id)) {
                      Database.table('absensis').where('id', '=', absen.id).delete();
                  }
              });
          }
          return true;
      });
      
      return ResponseFormat.success(null, 'Data absensi berhasil dihapus!');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

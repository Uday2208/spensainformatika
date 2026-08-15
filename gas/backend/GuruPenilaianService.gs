/**
 * GuruPenilaianService.gs
 * Migrasi dari GuruController (Logika Penilaian Harian / Nilai Keaktifan)
 */

const GuruPenilaianService = {
  
  /**
   * Mengambil inisialisasi data (Daftar Kelas)
   */
  getPenilaianInit: function(token) {
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
   * Mengambil data siswa beserta nilai harian dan status absensi
   */
  getPenilaianData: function(token, kelas_id, tanggal, pertemuan) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      if (!kelas_id || !tanggal || !pertemuan) {
          return ResponseFormat.error("Kelas, Tanggal, dan Pertemuan harus diisi.");
      }
      
      // 1. Cek apakah absensi sudah diisi untuk kelas & tanggal tersebut
      const absensis = Database.table('absensis').where('tanggal', '=', tanggal).get();
      const siswasDiKelas = Database.table('siswas').where('kelas_id', '=', kelas_id).get();
      
      let absensiDiisi = false;
      for (const siswa of siswasDiKelas) {
          if (absensis.some(a => a.siswa_id == siswa.id)) {
              absensiDiisi = true;
              break;
          }
      }
      
      if (!absensiDiisi) {
          return ResponseFormat.success({
              absensi_belum_diisi: true,
              siswas: []
          });
      }
      
      // 2. Ambil data nilai sebelumnya (jika ada)
      const penilaianHarian = Database.table('penilaian_harians')
          .where('tanggal', '=', tanggal)
          .where('pertemuan', '=', pertemuan)
          .get();
          
      const allUsers = Database.table('users').get();
      
      // 3. Gabungkan data (Siswa + Absensi + Penilaian Harian)
      let finalSiswas = siswasDiKelas.map(s => {
          const user = allUsers.find(u => u.id == s.user_id) || {};
          
          // Cari status absensi
          const ab = absensis.find(a => a.siswa_id == s.id);
          const absensiStatus = ab ? ab.status : 'alpha';
          const isHadir = ['hadir', 'dispen'].includes(absensiStatus.toLowerCase());
          
          // Cari nilai yang sudah ada (jika pernah dinilai sebelumnya)
          const nilaiData = penilaianHarian.find(n => n.siswa_id == s.id);
          
          let nilai = nilaiData ? nilaiData.nilai : (isHadir ? 80 : 70);
          let catatan = nilaiData ? nilaiData.catatan : '';
          
          return {
              id: s.id,
              nis: s.nis,
              nama: user.name || s.nama,
              absensiStatus: absensiStatus,
              isHadir: isHadir,
              nilai: nilai,
              catatan: catatan || ''
          };
      });
      
      return ResponseFormat.success({
          absensi_belum_diisi: false,
          siswas: finalSiswas
      });
      
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Menyimpan / Upsert Penilaian Harian
   */
  storePenilaian: function(token, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const { kelas_id, tanggal, pertemuan, nilai } = payload;
      
      if (!kelas_id || !tanggal || !pertemuan || !Array.isArray(nilai)) {
          return ResponseFormat.validationError({ general: ["Data tidak lengkap."] });
      }
      
      const now = new Date().toISOString();
      
      Database.transaction(() => {
          nilai.forEach(item => {
              // Upsert Logic: Check unique (siswa_id, tanggal, pertemuan)
              const existing = Database.table('penilaian_harians')
                  .where('siswa_id', '=', item.siswa_id)
                  .where('tanggal', '=', tanggal)
                  .where('pertemuan', '=', pertemuan)
                  .get();
                  
              if (existing.length > 0) {
                  // Update
                  Database.table('penilaian_harians').where('id', '=', existing[0].id).update({
                      nilai: item.nilai,
                      catatan: item.catatan,
                      updated_at: now
                  });
              } else {
                  // Insert
                  Database.table('penilaian_harians').insert({
                      siswa_id: item.siswa_id,
                      kelas_id: kelas_id,
                      tanggal: tanggal,
                      pertemuan: pertemuan,
                      nilai: item.nilai,
                      catatan: item.catatan || '',
                      created_at: now,
                      updated_at: now
                  });
              }
          });
          return true;
      });
      
      return ResponseFormat.success(null, "Penilaian harian berhasil disimpan!");
      
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

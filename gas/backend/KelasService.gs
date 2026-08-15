/**
 * KelasService.gs
 * Menangani operasi CRUD untuk Data Kelas (Modul 2)
 */

const KelasService = {
  
  /**
   * Mengambil daftar kelas dan jumlah siswa di dalamnya
   */
  getDataKelas: function(token) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const kelas = Database.table('kelas').get();
      const siswas = Database.table('siswas').get();
      
      // Hitung jumlah siswa per kelas
      const mappedKelas = kelas.map(k => {
        k.jumlah_siswa = siswas.filter(s => s.kelas_id == k.id).length;
        return k;
      });
      
      return ResponseFormat.success(mappedKelas);
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Menyimpan kelas baru
   */
  storeKelas: function(token, data) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const rules = {
        nama_kelas: 'required|unique:kelas,nama_kelas'
      };
      
      const errors = FormValidator.validate(data, rules);
      if (errors) return ResponseFormat.validationError(errors);
      
      const id = Database.table('kelas').insert({
        nama_kelas: data.nama_kelas
      });
      
      return ResponseFormat.success({ id: id }, 'Kelas berhasil ditambahkan');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Menghapus kelas
   */
  deleteKelas: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const kelas = Database.table('kelas').find(id);
      if (!kelas) return ResponseFormat.error('Kelas tidak ditemukan');
      
      // Cascade Delete: Cari semua siswa di kelas ini
      const siswas = Database.table('siswas').where('kelas_id', '=', id).get();
      
      siswas.forEach(siswa => {
          // Gunakan helper khusus untuk menghapus data terkait dengan aman
          safeDelete('absensis', 'siswa_id', siswa.id);
          safeDelete('nilais', 'siswa_id', siswa.id);
          safeDelete('penilaian_harians', 'siswa_id', siswa.id);
          safeDelete('komentars', 'siswa_id', siswa.id);
          safeDelete('ujian_pesertas', 'siswa_id', siswa.id);
          safeDelete('jawaban_siswas', 'siswa_id', siswa.id);
          safeDelete('hasil_ujians', 'siswa_id', siswa.id);
          
          // Hapus user account siswa
          safeDelete('users', 'id', siswa.user_id);
      });
      
      // Hapus seluruh siswa di kelas
      safeDelete('siswas', 'kelas_id', id);
      
      // Terakhir, hapus kelas
      Database.table('kelas').where('id', '=', id).delete();
      
      return ResponseFormat.success(null, 'Kelas berhasil dihapus');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

// Helper untuk mencoba menghapus tabel secara aman (mengabaikan jika tabel belum ada di Spreadsheet)
function safeDelete(tableName, column, value) {
    try {
        Database.table(tableName).where(column, '=', value).delete();
    } catch (e) {
        // Abaikan error jika tabel belum dibuat (contoh: modul ujian belum diimplementasikan)
        if (e.message && e.message.includes('not found')) {
            return;
        }
        // Lempar error lain
        throw e;
    }
}

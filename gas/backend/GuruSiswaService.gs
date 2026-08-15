/**
 * GuruSiswaService.gs
 * Migrasi dari GuruController yang berhubungan dengan Data Siswa
 */

const GuruSiswaService = {
  
  getDataSiswa: function(token) {
    try {
      SessionManager.requireRole(token, 'guru'); // SECURITY ENHANCEMENT
      
      const siswas = Database.table('siswas').get();
      // Join dengan data user dan kelas layaknya eager loading di Laravel
      const detailedSiswas = siswas.map(siswa => {
        siswa.user = Database.table('users').find(siswa.user_id) || {};
        siswa.kelas = Database.table('kelas').find(siswa.kelas_id) || {};
        return siswa;
      });
      
      return ResponseFormat.success(detailedSiswas);
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  storeSiswa: function(token, data) {
    try {
      SessionManager.requireRole(token, 'guru'); // SECURITY ENHANCEMENT
      
      const rules = {
        name: 'required',
        username: 'required|unique:users,username',
        password: 'required|min:6',
        kelas_id: 'required|exists:kelas,id',
        nis: 'required|numeric|unique:siswas,nis'
      };
      
      const errors = FormValidator.validate(data, rules);
      if (errors) return ResponseFormat.validationError(errors);
      
      // Transaction wrapper: Insert user then insert siswa
      let userId, siswaId;
      Database.transaction(() => {
        userId = Database.table('users').insert({
          name: data.name,
          username: data.username,
          password: data.password, // Ideally hashed
          role: 'siswa'
        });
        
        siswaId = Database.table('siswas').insert({
          user_id: userId,
          kelas_id: data.kelas_id,
          nis: data.nis,
          nisn: data.nisn || '',
          nama: data.name,
          jk: data.jk || 'L'
        });
        return true;
      });
      
      return ResponseFormat.success({ siswa_id: siswaId }, 'Siswa created successfully');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  destroySiswa: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const siswa = Database.table('siswas').find(id);
      if (!siswa) return ResponseFormat.notFound('Siswa not found');
      
      Database.transaction(() => {
        // Cascade Delete menggunakan helper safeDeleteSiswa agar tidak error jika tabel belum ada
        safeDeleteSiswa('absensis', 'siswa_id', id);
        safeDeleteSiswa('nilais', 'siswa_id', id);
        safeDeleteSiswa('penilaian_harians', 'siswa_id', id);
        safeDeleteSiswa('komentars', 'siswa_id', id);
        safeDeleteSiswa('ujian_pesertas', 'siswa_id', id);
        safeDeleteSiswa('jawaban_siswas', 'siswa_id', id);
        safeDeleteSiswa('hasil_ujians', 'siswa_id', id);
        
        // Hapus akun pengguna (user) siswa ini
        safeDeleteSiswa('users', 'id', siswa.user_id);
        // Hapus data pokok siswa
        safeDeleteSiswa('siswas', 'id', id);
        return true;
      });
      
      return ResponseFormat.success(null, 'Siswa deleted successfully');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  updateSiswa: function(token, id, data) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const siswa = Database.table('siswas').find(id);
      if (!siswa) return ResponseFormat.notFound('Siswa not found');
      
      const rules = {
        name: 'required',
        kelas_id: 'required|exists:kelas,id',
        nis: 'required|numeric'
      };
      
      const errors = FormValidator.validate(data, rules);
      if (errors) return ResponseFormat.validationError(errors);
      
      // Check if new NIS already exists and belongs to a different student
      const existingSiswa = Database.table('siswas').where('nis', '=', data.nis).get();
      if (existingSiswa.length > 0 && existingSiswa[0].id !== id) {
          return ResponseFormat.validationError({ nis: ['NIS sudah digunakan oleh siswa lain'] });
      }

      Database.transaction(() => {
        // Update user (name only)
        Database.table('users').where('id', '=', siswa.user_id).update({
          name: data.name
        });
        
        // Update siswa
        Database.table('siswas').where('id', '=', id).update({
          kelas_id: data.kelas_id,
          nis: data.nis,
          nama: data.name
        });
        return true;
      });
      
      return ResponseFormat.success(null, 'Data siswa berhasil diperbarui');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  resetPasswordSiswa: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const siswa = Database.table('siswas').find(id);
      if (!siswa) return ResponseFormat.notFound('Siswa not found');
      
      // Default password is NIS
      Database.table('users').where('id', '=', siswa.user_id).update({
        username: siswa.nis,
        password: siswa.nis
      });
      
      return ResponseFormat.success(null, 'Kata sandi berhasil direset ke NIS');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  importSiswaBulk: function(token, kelas_id, rows) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      // Validate inputs
      if (!kelas_id) return ResponseFormat.validationError({kelas_id: ['Kelas tidak valid']});
      if (!rows || !Array.isArray(rows) || rows.length === 0) return ResponseFormat.validationError({file: ['File CSV kosong atau format tidak valid']});
      
      let successCount = 0;
      let skippedCount = 0;
      
      Database.transaction(() => {
        rows.forEach(row => {
          const nis = row.nis;
          const nama = row.nama;
          
          if (!nis || !nama) return; // Skip invalid row
          
          // Check if NIS already exists
          const existing = Database.table('siswas').where('nis', '=', nis).get();
          if (existing.length > 0) {
            skippedCount++;
            return;
          }
          
          // Create user
          const userId = Database.table('users').insert({
            name: nama,
            username: nis,
            password: nis,
            role: 'siswa'
          });
          
          // Create siswa
          Database.table('siswas').insert({
            user_id: userId,
            kelas_id: kelas_id,
            nis: nis,
            nisn: '',
            nama: nama,
            jk: 'L'
          });
          
          successCount++;
        });
        return true;
      });
      
      return ResponseFormat.success({success: successCount, skipped: skippedCount}, `Berhasil mengimpor ${successCount} siswa. Diabaikan: ${skippedCount} (NIS duplikat).`);
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  getExportSiswa: function(token, kelas_id) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      if (!kelas_id) return ResponseFormat.error('Pilih kelas untuk diekspor');
      
      const siswas = Database.table('siswas').where('kelas_id', '=', kelas_id).get();
      
      // Eager load user data to get the name if 'nama' in siswas is not updated
      const exportData = siswas.map(siswa => {
        const user = Database.table('users').find(siswa.user_id) || {};
        return {
          nis: siswa.nis,
          nama: user.name || siswa.nama
        };
      });
      
      return ResponseFormat.success(exportData);
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

// Helper untuk mencoba menghapus tabel secara aman (mengabaikan jika tabel belum ada di Spreadsheet)
function safeDeleteSiswa(tableName, column, value) {
    try {
        Database.table(tableName).where(column, '=', value).delete();
    } catch (e) {
        // Abaikan error jika tabel belum dibuat
        if (e.message && e.message.includes('not found')) {
            return;
        }
        // Lempar error lain
        throw e;
    }
}

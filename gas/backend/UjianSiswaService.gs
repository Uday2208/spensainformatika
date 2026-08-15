/**
 * UjianSiswaService.gs
 * Migrasi dari UjianSiswaController.php (Siswa Side)
 */

const UjianSiswaService = {
  
  masukUjian: function(token, ujianId, tokenMasuk) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      const siswa = Database.table('siswas').where('user_id', '=', user.id).first();
      
      const ujian = Database.table('ujians').find(ujianId);
      if (!ujian || ujian.status !== 'aktif') return ResponseFormat.error('Ujian tidak aktif');
      
      if (String(ujian.token).toUpperCase() !== String(tokenMasuk).toUpperCase()) {
        return ResponseFormat.error('Token ujian salah');
      }
      
      // Validasi kelas siswa vs ujian_kelas
      const kelasAkses = Database.table('ujian_kelas').where('ujian_id', '=', ujianId).get();
      const isAllowed = kelasAkses.some(k => k.kelas_id == siswa.kelas_id);
      if (!isAllowed) return ResponseFormat.forbidden('Anda tidak terdaftar di kelas ujian ini');
      
      // Check if already started or finished
      const hasil = Database.table('hasil_ujians')
                            .where('siswa_id', '=', siswa.id)
                            .where('ujian_id', '=', ujianId)
                            .first();
                            
      if (hasil && (hasil.status === 'selesai' || hasil.status === 'dinilai')) {
        return ResponseFormat.error('Anda sudah menyelesaikan ujian ini');
      }
      
      if (!hasil) {
        Database.table('hasil_ujians').insert({
          siswa_id: siswa.id,
          ujian_id: ujianId,
          status: 'mengerjakan',
          started_at: new Date().toISOString()
        });
        
        Database.table('log_ujians').insert({
          siswa_id: siswa.id,
          ujian_id: ujianId,
          event: 'mulai',
          detail: 'Siswa mulai mengerjakan ujian'
        });
      }
      
      return ResponseFormat.success(null, 'Berhasil masuk ujian');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  saveAnswer: function(token, ujianId, jawabanPayload) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      const siswa = Database.table('siswas').where('user_id', '=', user.id).first();
      
      const hasil = Database.table('hasil_ujians')
                            .where('siswa_id', '=', siswa.id)
                            .where('ujian_id', '=', ujianId)
                            .first();
                            
      if (!hasil || hasil.status !== 'mengerjakan') {
        return ResponseFormat.error('Ujian sudah selesai atau belum dimulai');
      }
      
      // Ambil daftar soal valid milik ujian ini (SECURITY ENHANCEMENT)
      const validSoals = Database.table('soals').where('ujian_id', '=', ujianId).get();
      const validSoalIds = validSoals.map(s => s.id.toString());
      
      Database.transaction(() => {
        for (const [soalId, jawabanTeks] of Object.entries(jawabanPayload)) {
          if (validSoalIds.includes(String(soalId))) {
            const existing = Database.table('jawaban_siswas')
                                     .where('siswa_id', '=', siswa.id)
                                     .where('soal_id', '=', parseInt(soalId))
                                     .first();
            if (existing) {
              Database.table('jawaban_siswas').update({
                id: existing.id,
                jawaban: jawabanTeks
              });
            } else {
              Database.table('jawaban_siswas').insert({
                siswa_id: siswa.id,
                ujian_id: ujianId,
                soal_id: parseInt(soalId),
                jawaban: jawabanTeks
              });
            }
          }
        }
        return true;
      });
      
      return ResponseFormat.success(null, 'Jawaban disimpan (Auto-save)');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  submitUjian: function(token, ujianId, jawabanPayload = {}) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      const siswa = Database.table('siswas').where('user_id', '=', user.id).first();
      
      const hasil = Database.table('hasil_ujians')
                            .where('siswa_id', '=', siswa.id)
                            .where('ujian_id', '=', ujianId)
                            .first();
                            
      if (!hasil) return ResponseFormat.error('Tidak ada sesi ujian aktif');
      if (hasil.status !== 'mengerjakan') return ResponseFormat.success(null, 'Ujian sudah dikumpulkan sebelumnya');
      
      // Save remaining payload first (if any)
      this.saveAnswer(token, ujianId, jawabanPayload);
      
      // Kalkulasi PG
      const soals = Database.table('soals').where('ujian_id', '=', ujianId).get();
      const soalPg = soals.filter(s => s.tipe === 'pg');
      const totalBobotPg = soalPg.reduce((sum, s) => sum + parseInt(s.bobot), 0);
      let bobotBenar = 0;
      
      const jawabans = Database.table('jawaban_siswas')
                               .where('siswa_id', '=', siswa.id)
                               .where('ujian_id', '=', ujianId)
                               .get();
                               
      Database.transaction(() => {
        for (const soal of soalPg) {
          const jawaban = jawabans.find(j => j.soal_id === soal.id);
          if (jawaban) {
            const isCorrect = String(jawaban.jawaban).trim().toLowerCase() === String(soal.jawaban_benar).trim().toLowerCase();
            if (isCorrect) bobotBenar += parseInt(soal.bobot);
            
            Database.table('jawaban_siswas').update({
              id: jawaban.id,
              is_correct: isCorrect
            });
          }
        }
        
        const nilaiPg = totalBobotPg > 0 ? (bobotBenar / totalBobotPg) * 100 : 0;
        const hasEssay = soals.some(s => s.tipe === 'essay');
        
        Database.table('hasil_ujians').update({
          id: hasil.id,
          nilai_pg: parseFloat(nilaiPg.toFixed(2)),
          nilai_akhir: hasEssay ? 0 : parseFloat(nilaiPg.toFixed(2)),
          status: 'selesai',
          finished_at: new Date().toISOString()
        });
        
        Database.table('log_ujians').insert({
          siswa_id: siswa.id,
          ujian_id: ujianId,
          event: 'submit',
          detail: 'Siswa submit ujian'
        });
        return true;
      });
      
      return ResponseFormat.success(null, 'Ujian berhasil dikumpulkan');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

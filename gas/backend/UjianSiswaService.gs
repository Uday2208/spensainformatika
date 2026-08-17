/**
 * UjianSiswaService.gs
 * Migrasi dari UjianSiswaController.php (Siswa Side CBT)
 */

const UjianSiswaService = {

  _safeGet: function(tableName) {
    try {
      var ss = Database.getSpreadsheet();
      var sheet = ss.getSheetByName(tableName);
      if (!sheet) return [];
      var data = Database.table(tableName).get() || [];
      return data.filter(function(row) {
        return row && row.id !== undefined && row.id !== null && String(row.id).trim() !== '';
      });
    } catch(e) {
      Logger.log("Warning safeGet " + tableName + ": " + e.message);
      return [];
    }
  },

  /**
   * Validasi token & registrasi mulai ujian
   */
  masukUjian: function(token, ujianId, tokenMasuk) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      var allSiswas = this._safeGet('siswas');
      const siswa = allSiswas.find(function(s) { return s.user_id == user.id; });
      if (!siswa) return ResponseFormat.error('Data siswa tidak ditemukan');
      
      var allUjians = this._safeGet('ujians');
      const ujian = allUjians.find(function(u) { return u.id == ujianId; });
      if (!ujian || ujian.status !== 'aktif') return ResponseFormat.error('Ujian tidak aktif atau sudah ditutup.');
      
      if (String(ujian.token).trim().toUpperCase() !== String(tokenMasuk).trim().toUpperCase()) {
        return ResponseFormat.error('Token ujian yang Anda masukkan salah.');
      }
      
      // Validasi kelas siswa vs ujian_kelas
      var allUjianKelas = this._safeGet('ujian_kelas');
      const isAllowed = allUjianKelas.some(function(k) {
        return k.ujian_id == ujianId && (k.kelas_id == siswa.kelas_id || String(k.kelas_id) == String(siswa.kelas_id));
      });
      if (!isAllowed) return ResponseFormat.forbidden('Anda tidak terdaftar di rombel kelas peserta ujian ini.');
      
      // Cek apakah sudah selesai
      var allHasil = this._safeGet('hasil_ujians');
      const hasil = allHasil.find(function(h) { return h.siswa_id == siswa.id && h.ujian_id == ujianId; });
      if (hasil && (hasil.status === 'selesai' || hasil.status === 'dinilai')) {
        return ResponseFormat.error('Anda sudah menyelesaikan ujian ini.');
      }
      
      if (!hasil) {
        var nowIso = new Date().toISOString();
        Database.table('hasil_ujians').insert({
          siswa_id: siswa.id,
          ujian_id: ujianId,
          status: 'mengerjakan',
          started_at: nowIso,
          tab_switch_count: 0,
          created_at: nowIso,
          updated_at: nowIso
        });
        
        try {
          Database.table('log_ujians').insert({
            siswa_id: siswa.id,
            ujian_id: ujianId,
            event: 'mulai',
            detail: 'Siswa mulai mengerjakan ujian',
            created_at: nowIso
          });
        } catch(e){}
      }
      
      return ResponseFormat.success(null, 'Berhasil masuk ke ruang ujian');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengambil butir soal untuk dikerjakan siswa (Kunci jawaban dihapus demi keamanan)
   */
  getSoalUjian: function(token, ujianId) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      var allSiswas = this._safeGet('siswas');
      const siswa = allSiswas.find(function(s) { return s.user_id == user.id; });
      if (!siswa) return ResponseFormat.error('Data siswa tidak ditemukan');

      var allUjians = this._safeGet('ujians');
      const ujian = allUjians.find(function(u) { return u.id == ujianId; });
      if (!ujian) return ResponseFormat.error('Ujian tidak ditemukan');

      var allHasil = this._safeGet('hasil_ujians');
      const hasil = allHasil.find(function(h) { return h.siswa_id == siswa.id && h.ujian_id == ujianId; });
      if (!hasil) return ResponseFormat.error('Silakan masukkan token ujian terlebih dahulu.');

      var allSoals = this._safeGet('soals');
      var soals = allSoals.filter(function(s) { return s.ujian_id == ujianId; });
      soals.sort(function(a, b) { return (parseInt(a.urutan) || 0) - (parseInt(b.urutan) || 0); });

      // Ambil jawaban yang tersimpan sebelumnya
      var allJawaban = this._safeGet('jawaban_siswas');
      var myJawaban = allJawaban.filter(function(j) { return j.siswa_id == siswa.id && j.ujian_id == ujianId; });
      var jwbMap = {};
      myJawaban.forEach(function(j) {
        jwbMap[j.soal_id] = j.jawaban || '';
      });

      // Sanitasi soal (hapus kunci jawaban benar agar tidak bisa di-inspect via browser console)
      var clientSoals = soals.map(function(s, idx) {
        return {
          no: idx + 1,
          id: s.id,
          tipe: String(s.tipe || 'pg'),
          pertanyaan: String(s.pertanyaan || ''),
          opsi_a: String(s.opsi_a || ''),
          opsi_b: String(s.opsi_b || ''),
          opsi_c: String(s.opsi_c || ''),
          opsi_d: String(s.opsi_d || ''),
          bobot: parseInt(s.bobot) || 1,
          jawaban_terpilih: jwbMap[s.id] || ''
        };
      });

      return ResponseFormat.success({
        ujian: {
          id: ujian.id,
          judul: String(ujian.judul || ''),
          bab: String(ujian.bab || ''),
          durasi: parseInt(ujian.durasi) || 60,
          started_at: hasil.started_at
        },
        hasil: {
          id: hasil.id,
          status: String(hasil.status || 'mengerjakan'),
          tab_switch_count: parseInt(hasil.tab_switch_count) || 0
        },
        soals: clientSoals,
        jawaban_tersimpan: jwbMap
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Autosave jawaban siswa per butir soal
   */
  saveAnswer: function(token, ujianId, jawabanPayload) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      var allSiswas = this._safeGet('siswas');
      const siswa = allSiswas.find(function(s) { return s.user_id == user.id; });
      if (!siswa) return ResponseFormat.error('Data siswa tidak ditemukan');
      
      var allHasil = this._safeGet('hasil_ujians');
      const hasil = allHasil.find(function(h) { return h.siswa_id == siswa.id && h.ujian_id == ujianId; });
      if (!hasil || hasil.status !== 'mengerjakan') {
        return ResponseFormat.error('Ujian sudah selesai atau belum dimulai');
      }
      
      var allJawaban = this._safeGet('jawaban_siswas');
      var nowIso = new Date().toISOString();

      for (var soalId in jawabanPayload) {
        var jawabanTeks = String(jawabanPayload[soalId] || '').trim();
        var existing = allJawaban.find(function(j) {
          return j.siswa_id == siswa.id && j.ujian_id == ujianId && j.soal_id == soalId;
        });

        if (existing) {
          Database.table('jawaban_siswas').where('id', '=', existing.id).update({
            jawaban: jawabanTeks,
            updated_at: nowIso
          });
        } else {
          Database.table('jawaban_siswas').insert({
            siswa_id: siswa.id,
            ujian_id: ujianId,
            soal_id: parseInt(soalId),
            jawaban: jawabanTeks,
            created_at: nowIso,
            updated_at: nowIso
          });
        }
      }
      
      return ResponseFormat.success(null, 'Jawaban berhasil disimpan otomatis');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  /**
   * Submit / Kumpulkan lembar ujian akhir
   */
  submitUjian: function(token, ujianId, jawabanPayload) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      var allSiswas = this._safeGet('siswas');
      const siswa = allSiswas.find(function(s) { return s.user_id == user.id; });
      if (!siswa) return ResponseFormat.error('Data siswa tidak ditemukan');
      
      var allHasil = this._safeGet('hasil_ujians');
      const hasil = allHasil.find(function(h) { return h.siswa_id == siswa.id && h.ujian_id == ujianId; });
      if (!hasil) return ResponseFormat.error('Tidak ada sesi ujian aktif');
      if (hasil.status === 'selesai' || hasil.status === 'dinilai') {
        return ResponseFormat.success(null, 'Ujian sudah dikumpulkan sebelumnya');
      }
      
      // Simpan sisa jawaban terakhir jika ada
      if (jawabanPayload) {
        this.saveAnswer(token, ujianId, jawabanPayload);
      }
      
      var allSoals = this._safeGet('soals');
      const soals = allSoals.filter(function(s) { return s.ujian_id == ujianId; });
      const soalPg = soals.filter(function(s) { return s.tipe === 'pg'; });
      const totalBobotPg = soalPg.reduce(function(sum, s) { return sum + (parseFloat(s.bobot) || 1); }, 0);
      
      var allJawaban = this._safeGet('jawaban_siswas');
      const jawabans = allJawaban.filter(function(j) { return j.siswa_id == siswa.id && j.ujian_id == ujianId; });
      
      var bobotBenar = 0;
      soalPg.forEach(function(soal) {
        const jwb = jawabans.find(function(j) { return j.soal_id == soal.id; });
        if (jwb) {
          const isCorrect = String(jwb.jawaban).trim().toLowerCase() === String(soal.jawaban_benar).trim().toLowerCase();
          if (isCorrect) bobotBenar += (parseFloat(soal.bobot) || 1);
          try {
            Database.table('jawaban_siswas').where('id', '=', jwb.id).update({ is_correct: isCorrect });
          } catch(e){}
        }
      });
      
      const nilaiPg = totalBobotPg > 0 ? (bobotBenar / totalBobotPg) * 100 : 0;
      const hasEssay = soals.some(function(s) { return s.tipe === 'essay'; });
      const nowIso = new Date().toISOString();
      
      Database.table('hasil_ujians').where('id', '=', hasil.id).update({
        nilai_pg: parseFloat(nilaiPg.toFixed(2)),
        nilai_akhir: hasEssay ? 0 : parseFloat(nilaiPg.toFixed(2)),
        status: hasEssay ? 'selesai' : 'dinilai',
        finished_at: nowIso,
        updated_at: nowIso
      });
      
      try {
        Database.table('log_ujians').insert({
          siswa_id: siswa.id,
          ujian_id: ujianId,
          event: 'submit',
          detail: 'Siswa submit ujian akhir',
          created_at: nowIso
        });
      } catch(e){}
      
      return ResponseFormat.success({
        nilai_pg: parseFloat(nilaiPg.toFixed(2)),
        has_essay: hasEssay
      }, 'Ujian berhasil dikumpulkan!');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mencatat indikasi kecurangan (Tab-Switch / Minimize)
   */
  logKecurangan: function(token, ujianId, event, detail) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      var allSiswas = this._safeGet('siswas');
      const siswa = allSiswas.find(function(s) { return s.user_id == user.id; });
      if (!siswa) return ResponseFormat.error('Data siswa tidak ditemukan');

      var allHasil = this._safeGet('hasil_ujians');
      const hasil = allHasil.find(function(h) { return h.siswa_id == siswa.id && h.ujian_id == ujianId; });
      if (hasil && hasil.status === 'mengerjakan') {
        var currentCount = parseInt(hasil.tab_switch_count) || 0;
        Database.table('hasil_ujians').where('id', '=', hasil.id).update({
          tab_switch_count: currentCount + 1,
          updated_at: new Date().toISOString()
        });

        try {
          Database.table('log_ujians').insert({
            siswa_id: siswa.id,
            ujian_id: ujianId,
            event: event || 'tab_switch',
            detail: detail || 'Siswa berpindah tab / jendela peramban',
            created_at: new Date().toISOString()
          });
        } catch(e){}
      }

      return ResponseFormat.success(null, 'Log tercatat');
    } catch(e) {
      return ResponseFormat.error(e.message);
    }
  }
};

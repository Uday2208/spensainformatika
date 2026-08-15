/**
 * UjianService.gs
 * Migrasi dari UjianController.php (Guru Side)
 */

const UjianService = {
  
  createUjian: function(token, data) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const rules = {
        judul: 'required',
        bab: 'required',
        tanggal: 'required',
        durasi: 'required|numeric|min:10',
        kelas_id: 'required' // asumsikan array/comma separated ids
      };
      
      const errors = FormValidator.validate(data, rules);
      if (errors) return ResponseFormat.validationError(errors);
      
      let ujianId;
      Database.transaction(() => {
        ujianId = Database.table('ujians').insert({
          judul: data.judul,
          bab: data.bab,
          tanggal: data.tanggal,
          durasi: data.durasi,
          status: 'draft'
        });
        
        // Pivot table ujian_kelas
        const kelasIds = String(data.kelas_id).split(',');
        kelasIds.forEach(id => {
          Database.table('ujian_kelas').insert({
            ujian_id: ujianId,
            kelas_id: parseInt(id.trim())
          });
        });
        
        return true;
      });
      
      return ResponseFormat.success({ ujian_id: ujianId }, 'Ujian created successfully');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  storeSoal: function(token, ujianId, data) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const ujian = Database.table('ujians').find(ujianId);
      if (!ujian) return ResponseFormat.notFound('Ujian not found');
      
      const rules = {
        tipe: 'required|in:pg,essay',
        pertanyaan: 'required',
        bobot: 'required|numeric'
      };
      
      const errors = FormValidator.validate(data, rules);
      if (errors) return ResponseFormat.validationError(errors);
      
      if (data.tipe === 'pg' && !data.jawaban_benar) {
        return ResponseFormat.validationError({ jawaban_benar: ['Jawaban benar required for PG'] });
      }
      
      const soalId = Database.table('soals').insert({
        ujian_id: ujianId,
        tipe: data.tipe,
        pertanyaan: data.pertanyaan,
        opsi_a: data.opsi_a || '',
        opsi_b: data.opsi_b || '',
        opsi_c: data.opsi_c || '',
        opsi_d: data.opsi_d || '',
        jawaban_benar: data.jawaban_benar || '',
        bobot: data.bobot,
        urutan: data.urutan || 0
      });
      
      return ResponseFormat.success({ soal_id: soalId }, 'Soal added successfully');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  activateUjian: function(token, ujianId) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const ujian = Database.table('ujians').find(ujianId);
      if (!ujian) return ResponseFormat.notFound('Ujian not found');
      
      // Generate 6-char token
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let examToken = '';
      for (let i = 0; i < 6; i++) {
        examToken += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      
      Database.table('ujians').update({
        id: ujianId,
        status: 'aktif',
        token: examToken,
        token_expired_at: new Date(new Date().getTime() + (24 * 60 * 60 * 1000)).toISOString() // 24 jam
      });
      
      return ResponseFormat.success({ token: examToken }, 'Ujian activated successfully');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },
  
  finalisasi: function(token, ujianId) {
    try {
      SessionManager.requireRole(token, 'guru');
      
      const ujian = Database.table('ujians').find(ujianId);
      if (!ujian) return ResponseFormat.notFound();
      
      const hasEssay = Database.table('soals').where('ujian_id', '=', ujianId).where('tipe', '=', 'essay').exists();
      const hasilSiswas = Database.table('hasil_ujians').where('ujian_id', '=', ujianId).get();
      
      Database.transaction(() => {
        hasilSiswas.forEach(hasil => {
          let nilaiAkhir = parseFloat(hasil.nilai_pg);
          if (hasEssay) {
            // Asumsi bobot PG dan Essay 50:50 jika keduanya ada, sesuai standar, 
            // Namun sebaiknya mengikuti logika bobot Laravel spesifik. 
            // Jika di audit Laravel essay ditambahkan langsung atau dirata-rata:
            nilaiAkhir = (parseFloat(hasil.nilai_pg) + parseFloat(hasil.nilai_essay || 0)) / 2;
          }
          
          Database.table('hasil_ujians').update({
            id: hasil.id,
            nilai_akhir: nilaiAkhir,
            status: 'dinilai'
          });
          
          // Sinkronisasi ke tabel nilais utama
          Database.table('nilais').insert({
            siswa_id: hasil.siswa_id,
            bab: ujian.bab,
            ulangan: nilaiAkhir
          });
        });
        
        Database.table('ujians').update({ id: ujianId, status: 'selesai' });
        return true;
      });
      
      return ResponseFormat.success(null, 'Ujian finalized successfully');
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

/**
 * UjianService.gs
 * Backend service untuk Ujian Harian & Hasil Ujian (Guru Side)
 * Migrasi dari UjianController.php
 */

const UjianService = {

  /**
   * Auto-sync header kolom & auto-create worksheet dengan ekspansi lebar sheet aman
   */
  _ensureUjianColumns: function() {
    try {
      var ss = Database.getSpreadsheet();
      var tables = {
        'ujians': ['id', 'judul', 'bab', 'tanggal', 'durasi', 'status', 'token', 'token_expired_at', 'created_at', 'updated_at'],
        'ujian_kelas': ['id', 'ujian_id', 'kelas_id'],
        'soals': ['id', 'ujian_id', 'tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'jawaban_benar', 'bobot', 'urutan', 'created_at', 'updated_at'],
        'hasil_ujians': ['id', 'siswa_id', 'ujian_id', 'nilai_pg', 'nilai_essay', 'nilai_akhir', 'status', 'started_at', 'finished_at', 'tab_switch_count', 'created_at', 'updated_at'],
        'jawaban_siswas': ['id', 'siswa_id', 'ujian_id', 'soal_id', 'jawaban', 'is_correct', 'created_at', 'updated_at'],
        'log_ujians': ['id', 'siswa_id', 'ujian_id', 'event', 'detail', 'created_at'],
        'nilais': ['id', 'siswa_id', 'bab', 'tugas', 'quiz', 'proyek', 'ulangan', 'nilai_akhir', 'created_at', 'updated_at'],
        'settings': ['id', 'key', 'value', 'created_at', 'updated_at']
      };

      for (var tableName in tables) {
        var sheet = ss.getSheetByName(tableName);
        var reqCols = tables[tableName];

        if (!sheet) {
          sheet = ss.insertSheet(tableName);
          var maxCols = sheet.getMaxColumns();
          if (reqCols.length > maxCols) {
            sheet.insertColumnsAfter(maxCols, reqCols.length - maxCols);
          }
          sheet.getRange(1, 1, 1, reqCols.length).setValues([reqCols]);
        } else {
          var lastCol = sheet.getLastColumn();
          var headers = lastCol > 0 ? sheet.getRange(1, 1, 1, lastCol).getValues()[0] : [];
          var appended = false;

          reqCols.forEach(function(col) {
            if (headers.indexOf(col) === -1) {
              headers.push(col);
              appended = true;
            }
          });

          if (appended || headers.length === 0) {
            var maxCols = sheet.getMaxColumns();
            var targetLength = headers.length > 0 ? headers.length : reqCols.length;
            var targetHeaders = headers.length > 0 ? headers : reqCols;

            if (targetLength > maxCols) {
              sheet.insertColumnsAfter(maxCols, targetLength - maxCols);
            }
            sheet.getRange(1, 1, 1, targetLength).setValues([targetHeaders]);
          }
        }
      }
    } catch(e) {
      Logger.log("Error _ensureUjianColumns: " + e.message);
    }
  },

  /**
   * Helper safe query table untuk menyaring baris kosong dan mencegah exception
   */
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
   * Helper normalisasi format tanggal ke string bersih (YYYY-MM-DD)
   */
  _formatDateStr: function(val) {
    if (!val) return '';
    if (val instanceof Date) {
      return val.toISOString().split('T')[0];
    }
    var str = String(val).trim();
    if (str.indexOf('T') !== -1) return str.split('T')[0];
    return str;
  },

  /**
   * Helper normalisasi ISO string
   */
  _formatIsoStr: function(val) {
    if (!val) return '';
    if (val instanceof Date) {
      return val.toISOString();
    }
    return String(val);
  },

  /**
   * Helper pembuat token acak 6 karakter unik
   */
  _generateToken: function() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    var token = '';
    for (var i = 0; i < 6; i++) {
      token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return token;
  },

  /**
   * Helper hitung nilai PG siswa
   */
  _hitungNilaiPg: function(ujianId, siswaId, soalPgList, totalBobotPg) {
    if (!soalPgList || soalPgList.length === 0 || totalBobotPg <= 0) {
      return { nilai_pg: 0, bobot_benar: 0 };
    }

    var allJawaban = this._safeGet('jawaban_siswas');
    var jawabanSiswas = allJawaban.filter(function(j) {
      return j.ujian_id == ujianId && j.siswa_id == siswaId;
    });

    var jawabanMap = {};
    jawabanSiswas.forEach(function(j) {
      jawabanMap[j.soal_id] = j;
    });

    var bobotBenar = 0;
    soalPgList.forEach(function(soal) {
      var jwb = jawabanMap[soal.id];
      if (jwb && String(jwb.jawaban).trim().toLowerCase() === String(soal.jawaban_benar).trim().toLowerCase()) {
        bobotBenar += (parseFloat(soal.bobot) || 1);
        if (jwb.is_correct !== true && jwb.is_correct !== 'true' && jwb.is_correct !== 1) {
          try {
            Database.table('jawaban_siswas').where('id', '=', jwb.id).update({ is_correct: true });
          } catch(e){}
        }
      } else if (jwb) {
        if (jwb.is_correct !== false && jwb.is_correct !== 'false' && jwb.is_correct !== 0) {
          try {
            Database.table('jawaban_siswas').where('id', '=', jwb.id).update({ is_correct: false });
          } catch(e){}
        }
      }
    });

    var nilaiPg = (bobotBenar / totalBobotPg) * 100;
    return {
      nilai_pg: parseFloat(nilaiPg.toFixed(2)),
      bobot_benar: bobotBenar
    };
  },

  // ==========================================
  // MANAJEMEN UJIAN (CRUD & SETTING)
  // ==========================================

  /**
   * Mengambil data inisialisasi daftar ujian (Panel Guru)
   */
  getUjianInit: function(token, statusFilter) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var allUjians = this._safeGet('ujians');
      var allKelas = this._safeGet('kelas');
      var allUjianKelas = this._safeGet('ujian_kelas');
      var allSoals = this._safeGet('soals');
      var allHasil = this._safeGet('hasil_ujians');
      var allNilais = this._safeGet('nilais');

      // Daftar bab unik dari nilais & ujians
      var distinctBab = [];
      allNilais.forEach(function(n) {
        if (n.bab && distinctBab.indexOf(String(n.bab).trim()) === -1) {
          distinctBab.push(String(n.bab).trim());
        }
      });
      allUjians.forEach(function(u) {
        if (u.bab && distinctBab.indexOf(String(u.bab).trim()) === -1) {
          distinctBab.push(String(u.bab).trim());
        }
      });
      distinctBab.sort();

      // Filter status jika ada
      if (statusFilter && String(statusFilter).trim() !== '') {
        allUjians = allUjians.filter(function(u) {
          return String(u.status || 'draft').trim().toLowerCase() === String(statusFilter).trim().toLowerCase();
        });
      }

      // Sort DESC
      allUjians.sort(function(a, b) {
        var dateA = new Date(a.created_at || 0).getTime();
        var dateB = new Date(b.created_at || 0).getTime();
        if (dateB !== dateA) return dateB - dateA;
        return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
      });

      // Map data pendukung dengan sanitasi serialisasi aman
      var processed = allUjians.map(function(u) {
        var targetKelasIds = allUjianKelas.filter(function(uk) {
          return uk.ujian_id == u.id;
        }).map(function(uk) { return uk.kelas_id; });

        var targetKelas = allKelas.filter(function(k) {
          return targetKelasIds.indexOf(k.id) !== -1 || targetKelasIds.indexOf(String(k.id)) !== -1;
        }).map(function(k) {
          return { id: k.id, nama_kelas: String(k.nama_kelas || '') };
        });

        var uSoals = allSoals.filter(function(s) { return s.ujian_id == u.id; });
        var essayCount = uSoals.filter(function(s) { return s.tipe === 'essay'; }).length;

        var uHasil = allHasil.filter(function(h) { return h.ujian_id == u.id; });
        var selesaiCount = uHasil.filter(function(h) { return h.status === 'selesai' || h.status === 'dinilai'; }).length;
        var perluKoreksi = essayCount > 0 && uHasil.filter(function(h) { return h.status === 'selesai'; }).length > 0;

        return {
          id: u.id,
          judul: String(u.judul || ''),
          bab: String(u.bab || ''),
          tanggal: UjianService._formatDateStr(u.tanggal),
          durasi: parseInt(u.durasi) || 60,
          status: String(u.status || 'draft'),
          token: String(u.token || ''),
          kelasList: targetKelas,
          soals_count: uSoals.length,
          soal_essay_count: essayCount,
          hasil_ujians_count: uHasil.length,
          selesai_count: selesaiCount,
          perlu_koreksi: perluKoreksi,
          created_at: UjianService._formatIsoStr(u.created_at)
        };
      });

      var serializedKelas = allKelas.map(function(k) {
        return { id: k.id, nama_kelas: String(k.nama_kelas || '') };
      });

      return ResponseFormat.success({
        ujians: processed,
        daftar_bab: distinctBab,
        all_kelas: serializedKelas
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir. Silakan login kembali.');
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengambil detail lengkap ujian (Bank Soal, Setting, Peserta Target)
   */
  getUjianDetail: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!id) return ResponseFormat.error("ID Ujian tidak valid.");

      var allUjians = this._safeGet('ujians');
      var ujian = allUjians.find(function(u) { return u.id == id; });
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allKelas = this._safeGet('kelas');
      var allUjianKelas = this._safeGet('ujian_kelas');
      var targetKelasIds = allUjianKelas.filter(function(uk) { return uk.ujian_id == id; }).map(function(uk) { return uk.kelas_id; });

      var targetKelas = allKelas.filter(function(k) {
        return targetKelasIds.indexOf(k.id) !== -1 || targetKelasIds.indexOf(String(k.id)) !== -1;
      }).map(function(k) {
        return { id: k.id, nama_kelas: String(k.nama_kelas || '') };
      });

      var allSoals = this._safeGet('soals');
      var soals = allSoals.filter(function(s) { return s.ujian_id == id; });
      soals.sort(function(a, b) {
        return (parseInt(a.urutan) || 0) - (parseInt(b.urutan) || 0);
      });

      var serializedSoals = soals.map(function(s) {
        return {
          id: s.id,
          ujian_id: s.ujian_id,
          tipe: String(s.tipe || 'pg'),
          pertanyaan: String(s.pertanyaan || ''),
          opsi_a: String(s.opsi_a || ''),
          opsi_b: String(s.opsi_b || ''),
          opsi_c: String(s.opsi_c || ''),
          opsi_d: String(s.opsi_d || ''),
          jawaban_benar: String(s.jawaban_benar || ''),
          bobot: parseInt(s.bobot) || 1,
          urutan: parseInt(s.urutan) || 0
        };
      });

      var serializedKelas = allKelas.map(function(k) {
        return { id: k.id, nama_kelas: String(k.nama_kelas || '') };
      });

      return ResponseFormat.success({
        ujian: {
          id: ujian.id,
          judul: String(ujian.judul || ''),
          bab: String(ujian.bab || ''),
          tanggal: UjianService._formatDateStr(ujian.tanggal),
          durasi: parseInt(ujian.durasi) || 60,
          status: String(ujian.status || 'draft'),
          token: String(ujian.token || ''),
          kelasList: targetKelas,
          kelas_ids: targetKelasIds
        },
        soals: serializedSoals,
        all_kelas: serializedKelas
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir. Silakan login kembali.');
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Membuat paket ujian baru (Status: draft)
   */
  storeUjian: function(token, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!payload || !payload.judul || !payload.bab) {
        return ResponseFormat.validationError({
          judul: !payload.judul ? ['Judul ujian wajib diisi.'] : undefined,
          bab: !payload.bab ? ['Materi / Bab wajib diisi.'] : undefined
        });
      }

      var now = new Date().toISOString();
      var dataToInsert = {
        judul: payload.judul.toString().trim(),
        bab: payload.bab.toString().trim(),
        durasi: 60,
        status: 'draft',
        created_at: now,
        updated_at: now
      };

      var newId = Database.table('ujians').insert(dataToInsert);

      return ResponseFormat.success({
        ujian_id: newId
      }, "Ujian berhasil dibuat! Silakan tambahkan soal dan atur Setting Ujian.");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Menyimpan Setting Ujian & Aktivasi Kelas Target
   */
  saveSettingUjian: function(token, id, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!id) return ResponseFormat.error("ID Ujian tidak valid.");
      var ujian = Database.table('ujians').find(id);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (!payload) payload = {};

      var durasi = parseInt(payload.durasi) || 60;
      if (durasi < 5 || durasi > 180) {
        return ResponseFormat.error("Durasi pengerjaan harus antara 5 sampai 180 menit.");
      }

      var now = new Date().toISOString();
      var updateData = {
        tanggal: payload.tanggal || '',
        durasi: durasi,
        updated_at: now
      };

      // Status update jika dikirim
      if (payload.status && ['draft', 'aktif', 'selesai'].indexOf(payload.status) !== -1) {
        if (payload.status === 'aktif') {
          var soalsCount = Database.table('soals').where('ujian_id', '=', id).count();
          if (soalsCount === 0) {
            return ResponseFormat.error("Tambahkan minimal 1 soal sebelum mengaktifkan ujian.");
          }
          if (!payload.kelas_ids || payload.kelas_ids.length === 0) {
            return ResponseFormat.error("Pilih minimal 1 kelas target peserta ujian.");
          }
        }
        updateData.status = payload.status;
      }

      // Token logic
      var tokenOption = payload.token_option || 'keep';
      if (tokenOption === 'random') {
        updateData.token = this._generateToken();
      } else if (tokenOption === 'custom' && payload.custom_token) {
        updateData.token = String(payload.custom_token).trim().toUpperCase();
      } else if (!ujian.token) {
        updateData.token = this._generateToken();
      }

      Database.table('ujians').where('id', '=', id).update(updateData);

      // Sync pivot ujian_kelas
      if (payload.kelas_ids !== undefined && Array.isArray(payload.kelas_ids)) {
        var oldMappings = this._safeGet('ujian_kelas').filter(function(uk) { return uk.ujian_id == id; });
        oldMappings.forEach(function(m) {
          try {
            Database.table('ujian_kelas').where('id', '=', m.id).delete();
          } catch(e){}
        });

        payload.kelas_ids.forEach(function(kId) {
          if (kId) {
            Database.table('ujian_kelas').insert({
              ujian_id: id,
              kelas_id: parseInt(kId)
            });
          }
        });
      }

      return ResponseFormat.success(null, "Setting Ujian berhasil diperbarui!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Menghapus paket ujian draft
   */
  destroyUjian: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!id) return ResponseFormat.error("ID Ujian tidak valid.");
      var ujian = Database.table('ujians').find(id);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (ujian.status !== 'draft') {
        return ResponseFormat.error("Hanya ujian berstatus draft yang bisa dihapus.");
      }

      var soals = this._safeGet('soals').filter(function(s) { return s.ujian_id == id; });
      soals.forEach(function(s) {
        try { Database.table('soals').where('id', '=', s.id).delete(); } catch(e){}
      });

      var pClass = this._safeGet('ujian_kelas').filter(function(pk) { return pk.ujian_id == id; });
      pClass.forEach(function(pk) {
        try { Database.table('ujian_kelas').where('id', '=', pk.id).delete(); } catch(e){}
      });

      Database.table('ujians').where('id', '=', id).delete();

      return ResponseFormat.success(null, "Ujian berhasil dihapus!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  // ==========================================
  // MANAJEMEN BUTIR SOAL (PG & ESSAY)
  // ==========================================

  /**
   * Menambahkan butir soal baru
   */
  storeSoal: function(token, ujianId, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!ujianId) return ResponseFormat.error("ID Ujian tidak valid.");
      var ujian = Database.table('ujians').find(ujianId);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (ujian.status !== 'draft') {
        return ResponseFormat.error("Tidak bisa menambah soal saat ujian sudah aktif atau selesai.");
      }

      if (!payload || !payload.pertanyaan || !payload.tipe) {
        return ResponseFormat.error("Pertanyaan dan tipe soal wajib diisi.");
      }

      var tipe = payload.tipe === 'essay' ? 'essay' : 'pg';
      var bobot = parseInt(payload.bobot) || (tipe === 'pg' ? 5 : 10);

      var existingSoals = this._safeGet('soals').filter(function(s) { return s.ujian_id == ujianId; });
      var maxUrutan = 0;
      existingSoals.forEach(function(s) {
        var u = parseInt(s.urutan) || 0;
        if (u > maxUrutan) maxUrutan = u;
      });

      var now = new Date().toISOString();
      var dataToInsert = {
        ujian_id: ujianId,
        tipe: tipe,
        pertanyaan: payload.pertanyaan.toString().trim(),
        bobot: bobot,
        urutan: maxUrutan + 1,
        created_at: now,
        updated_at: now
      };

      if (tipe === 'pg') {
        dataToInsert.opsi_a = (payload.opsi_a || '').toString().trim();
        dataToInsert.opsi_b = (payload.opsi_b || '').toString().trim();
        dataToInsert.opsi_c = (payload.opsi_c || '').toString().trim();
        dataToInsert.opsi_d = (payload.opsi_d || '').toString().trim();
        dataToInsert.jawaban_benar = (payload.jawaban_benar || 'a').toString().trim().toLowerCase();
      } else {
        dataToInsert.opsi_a = '';
        dataToInsert.opsi_b = '';
        dataToInsert.opsi_c = '';
        dataToInsert.opsi_d = '';
        dataToInsert.jawaban_benar = '';
      }

      var newSoalId = Database.table('soals').insert(dataToInsert);

      return ResponseFormat.success({
        soal_id: newSoalId
      }, "Soal berhasil ditambahkan!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengedit butir soal
   */
  updateSoal: function(token, ujianId, soalId, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!ujianId || !soalId) return ResponseFormat.error("ID tidak valid.");
      var ujian = Database.table('ujians').find(ujianId);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (ujian.status !== 'draft') {
        return ResponseFormat.error("Tidak bisa mengedit soal saat ujian sudah aktif atau selesai.");
      }

      var soal = Database.table('soals').find(soalId);
      if (!soal || soal.ujian_id != ujianId) {
        return ResponseFormat.error("Butir soal tidak ditemukan.");
      }

      var now = new Date().toISOString();
      var updateData = {
        pertanyaan: (payload.pertanyaan || soal.pertanyaan).toString().trim(),
        bobot: parseInt(payload.bobot) || soal.bobot,
        updated_at: now
      };

      if (soal.tipe === 'pg') {
        if (payload.opsi_a !== undefined) updateData.opsi_a = payload.opsi_a.toString().trim();
        if (payload.opsi_b !== undefined) updateData.opsi_b = payload.opsi_b.toString().trim();
        if (payload.opsi_c !== undefined) updateData.opsi_c = payload.opsi_c.toString().trim();
        if (payload.opsi_d !== undefined) updateData.opsi_d = payload.opsi_d.toString().trim();
        if (payload.jawaban_benar !== undefined) updateData.jawaban_benar = payload.jawaban_benar.toString().trim().toLowerCase();
      }

      Database.table('soals').where('id', '=', soalId).update(updateData);

      return ResponseFormat.success(null, "Soal berhasil diperbarui!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Menghapus butir soal
   */
  destroySoal: function(token, ujianId, soalId) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!ujianId || !soalId) return ResponseFormat.error("ID tidak valid.");
      var ujian = Database.table('ujians').find(ujianId);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (ujian.status !== 'draft') {
        return ResponseFormat.error("Tidak bisa menghapus soal saat ujian sudah aktif atau selesai.");
      }

      Database.table('soals').where('id', '=', soalId).delete();

      return ResponseFormat.success(null, "Soal berhasil dihapus!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  // ==========================================
  // TRANSISI STATUS & KONTROL UJIAN
  // ==========================================

  /**
   * Mengaktifkan ujian (Draft -> Aktif)
   */
  activateUjian: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var ujian = Database.table('ujians').find(id);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (ujian.status !== 'draft') {
        return ResponseFormat.error("Ujian sudah aktif atau selesai.");
      }

      var soalsCount = Database.table('soals').where('ujian_id', '=', id).count();
      if (soalsCount === 0) {
        return ResponseFormat.error("Tambahkan minimal 1 soal sebelum mengaktifkan ujian.");
      }

      var targetClasses = Database.table('ujian_kelas').where('ujian_id', '=', id).count();
      if (targetClasses === 0) {
        return ResponseFormat.error("Pilih minimal 1 kelas target peserta ujian di Setting Ujian.");
      }

      var examToken = ujian.token || this._generateToken();
      var now = new Date().toISOString();

      Database.table('ujians').where('id', '=', id).update({
        status: 'aktif',
        token: examToken,
        updated_at: now
      });

      return ResponseFormat.success({ token: examToken }, "Ujian berhasil diaktifkan! Token: " + examToken);
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengakhiri ujian (Aktif -> Selesai) + Auto-Submit peserta aktif
   */
  finishUjian: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var ujian = Database.table('ujians').find(id);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      if (ujian.status !== 'aktif') {
        return ResponseFormat.error("Hanya ujian yang sedang aktif yang bisa diakhiri.");
      }

      var allSoals = this._safeGet('soals').filter(function(s) { return s.ujian_id == id; });
      var soalsPg = allSoals.filter(function(s) { return s.tipe === 'pg'; });
      var totalBobotPg = 0;
      soalsPg.forEach(function(s) { totalBobotPg += (parseFloat(s.bobot) || 1); });
      var hasEssay = allSoals.filter(function(s) { return s.tipe === 'essay'; }).length > 0;

      var allHasil = this._safeGet('hasil_ujians');
      var hasilMengerjakan = allHasil.filter(function(h) {
        return h.ujian_id == id && h.status === 'mengerjakan';
      });

      var now = new Date().toISOString();

      // Auto submit dan hitung nilai PG untuk setiap siswa yang masih mengerjakan
      hasilMengerjakan.forEach(function(hasil) {
        var pgCalc = UjianService._hitungNilaiPg(id, hasil.siswa_id, soalsPg, totalBobotPg);
        var updatePayload = {
          status: 'selesai',
          nilai_pg: pgCalc.nilai_pg,
          finished_at: now,
          updated_at: now
        };
        if (!hasEssay) {
          updatePayload.nilai_akhir = pgCalc.nilai_pg;
          updatePayload.status = 'dinilai';
        }
        try {
          Database.table('hasil_ujians').where('id', '=', hasil.id).update(updatePayload);
        } catch(e){}
      });

      Database.table('ujians').where('id', '=', id).update({
        status: 'selesai',
        updated_at: now
      });

      return ResponseFormat.success({
        auto_submitted: hasilMengerjakan.length
      }, "Ujian berhasil diakhiri! " + hasilMengerjakan.length + " siswa otomatis dikumpulkan.");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  // ==========================================
  // MONITORING LIVE UJIAN
  // ==========================================

  /**
   * Mengambil data live monitoring peserta & log aktivitas
   */
  getMonitoringData: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var allUjians = this._safeGet('ujians');
      var ujian = allUjians.find(function(u) { return u.id == id; });
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allSiswas = this._safeGet('siswas');
      var allUsers = this._safeGet('users');
      var allKelas = this._safeGet('kelas');
      var userMap = {};
      allUsers.forEach(function(u) { userMap[u.id] = u; });
      var kelasMap = {};
      allKelas.forEach(function(k) { kelasMap[k.id] = k; });
      var siswaMap = {};
      allSiswas.forEach(function(s) {
        siswaMap[s.id] = {
          id: s.id,
          nama: (userMap[s.user_id] ? userMap[s.user_id].name : s.nama) || 'Siswa',
          kelas: (kelasMap[s.kelas_id] ? kelasMap[s.kelas_id].nama_kelas : '-')
        };
      });

      var allSoals = this._safeGet('soals');
      var totalSoals = allSoals.filter(function(s) { return s.ujian_id == id; }).length;

      var allHasil = this._safeGet('hasil_ujians');
      var hasilUjians = allHasil.filter(function(h) { return h.ujian_id == id; });
      var allJawabans = this._safeGet('jawaban_siswas').filter(function(j) { return j.ujian_id == id; });

      var jawabanCountMap = {};
      allJawabans.forEach(function(j) {
        if (j.jawaban !== undefined && j.jawaban !== null && String(j.jawaban).trim() !== '') {
          jawabanCountMap[j.siswa_id] = (jawabanCountMap[j.siswa_id] || 0) + 1;
        }
      });

      var pesertaList = hasilUjians.map(function(h) {
        var sInfo = siswaMap[h.siswa_id] || { nama: 'Siswa', kelas: '-' };
        return {
          id: h.id,
          siswa_id: h.siswa_id,
          nama_siswa: String(sInfo.nama || 'Siswa'),
          nama_kelas: String(sInfo.kelas || '-'),
          started_at: UjianService._formatIsoStr(h.started_at),
          finished_at: UjianService._formatIsoStr(h.finished_at),
          jawaban_count: jawabanCountMap[h.siswa_id] || 0,
          tab_switch_count: parseInt(h.tab_switch_count) || 0,
          status: String(h.status || 'mengerjakan'),
          nilai_pg: parseFloat(h.nilai_pg || 0),
          nilai_akhir: parseFloat(h.nilai_akhir || 0)
        };
      });

      pesertaList.sort(function(a, b) {
        if (a.status === 'mengerjakan' && b.status !== 'mengerjakan') return -1;
        if (b.status === 'mengerjakan' && a.status !== 'mengerjakan') return 1;
        return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
      });

      var allLogs = this._safeGet('log_ujians');
      var logs = allLogs.filter(function(l) { return l.ujian_id == id; });
      logs.sort(function(a, b) {
        return new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime();
      });

      var formattedLogs = logs.slice(0, 50).map(function(l) {
        var sInfo = siswaMap[l.siswa_id] || { nama: 'Siswa', kelas: '-' };
        return {
          id: l.id,
          nama_siswa: String(sInfo.nama || 'Siswa'),
          nama_kelas: String(sInfo.kelas || '-'),
          event: String(l.event || ''),
          detail: String(l.detail || ''),
          created_at: UjianService._formatIsoStr(l.created_at)
        };
      });

      return ResponseFormat.success({
        ujian: {
          id: ujian.id,
          judul: String(ujian.judul || ''),
          status: String(ujian.status || ''),
          token: String(ujian.token || ''),
          durasi: parseInt(ujian.durasi) || 60
        },
        total_soal: totalSoals,
        peserta: pesertaList,
        logs: formattedLogs
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir. Silakan login kembali.');
      return ResponseFormat.error(e.message);
    }
  },

  // ==========================================
  // KOREKSI ESSAY & PENILAIAN
  // ==========================================

  /**
   * Mengambil lembar kerja koreksi essay massal
   */
  getKoreksiData: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var allUjians = this._safeGet('ujians');
      var ujian = allUjians.find(function(u) { return u.id == id; });
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allSiswas = this._safeGet('siswas');
      var allUsers = this._safeGet('users');
      var allKelas = this._safeGet('kelas');
      var userMap = {};
      allUsers.forEach(function(u) { userMap[u.id] = u; });
      var kelasMap = {};
      allKelas.forEach(function(k) { kelasMap[k.id] = k; });
      var siswaMap = {};
      allSiswas.forEach(function(s) {
        siswaMap[s.id] = {
          id: s.id,
          nama: (userMap[s.user_id] ? userMap[s.user_id].name : s.nama) || 'Siswa',
          kelas: (kelasMap[s.kelas_id] ? kelasMap[s.kelas_id].nama_kelas : '-')
        };
      });

      var allSoals = this._safeGet('soals');
      var soalEssay = allSoals.filter(function(s) { return s.ujian_id == id && s.tipe === 'essay'; });
      soalEssay.sort(function(a, b) { return (parseInt(a.urutan) || 0) - (parseInt(b.urutan) || 0); });

      var allHasil = this._safeGet('hasil_ujians');
      var hasilUjians = allHasil.filter(function(h) { return h.ujian_id == id; });

      var allJawaban = this._safeGet('jawaban_siswas').filter(function(j) { return j.ujian_id == id; });

      var allSettings = this._safeGet('settings');
      var kkmSetting = allSettings.find(function(st) { return st.key === 'kkm_nilai'; });
      var kkm = kkmSetting ? (parseFloat(kkmSetting.value) || 75) : 75;

      var studentRows = hasilUjians.map(function(h) {
        var sInfo = siswaMap[h.siswa_id] || { nama: 'Siswa', kelas: '-' };

        var jwbMap = {};
        allJawaban.filter(function(j) { return j.siswa_id == h.siswa_id; }).forEach(function(j) {
          jwbMap[j.soal_id] = j.jawaban || '';
        });

        var essayAnswers = soalEssay.map(function(soal) {
          return {
            soal_id: soal.id,
            pertanyaan: String(soal.pertanyaan || ''),
            bobot: parseInt(soal.bobot) || 10,
            jawaban_siswa: String(jwbMap[soal.id] || '')
          };
        });

        return {
          hasil_id: h.id,
          siswa_id: h.siswa_id,
          nama_siswa: String(sInfo.nama || 'Siswa'),
          nama_kelas: String(sInfo.kelas || '-'),
          nilai_pg: parseFloat(h.nilai_pg || 0),
          nilai_essay: parseFloat(h.nilai_essay || 0),
          nilai_akhir: parseFloat(h.nilai_akhir || 0),
          status: String(h.status || 'selesai'),
          essay_answers: essayAnswers
        };
      });

      return ResponseFormat.success({
        ujian: {
          id: ujian.id,
          judul: String(ujian.judul || ''),
          bab: String(ujian.bab || ''),
          status: String(ujian.status || '')
        },
        soal_essay: soalEssay.map(function(s) {
          return { id: s.id, pertanyaan: String(s.pertanyaan || ''), bobot: parseInt(s.bobot) || 10 };
        }),
        kkm: kkm,
        peserta: studentRows
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir. Silakan login kembali.');
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Menyimpan nilai essay massal
   */
  storeKoreksiEssay: function(token, id, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      if (!id || !payload || !payload.nilai_essay) {
        return ResponseFormat.error("Data koreksi essay tidak valid.");
      }

      var ujian = Database.table('ujians').find(id);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allSoals = this._safeGet('soals').filter(function(s) { return s.ujian_id == id; });
      var bobotPg = 0;
      var bobotEssay = 0;
      allSoals.forEach(function(s) {
        if (s.tipe === 'pg') bobotPg += (parseFloat(s.bobot) || 1);
        else bobotEssay += (parseFloat(s.bobot) || 1);
      });
      var totalBobot = bobotPg + bobotEssay;

      var allHasil = this._safeGet('hasil_ujians');
      var hasilUjians = allHasil.filter(function(h) { return h.ujian_id == id; });
      var hasilMap = {};
      hasilUjians.forEach(function(h) { hasilMap[h.siswa_id] = h; });

      var now = new Date().toISOString();

      for (var siswaId in payload.nilai_essay) {
        var nEssay = parseFloat(payload.nilai_essay[siswaId] || 0);
        var hasil = hasilMap[siswaId];
        if (hasil) {
          var nPg = parseFloat(hasil.nilai_pg || 0);
          var nAkhir = 0;
          if (totalBobot > 0) {
            nAkhir = ((nPg * bobotPg) + (nEssay * bobotEssay)) / totalBobot;
          } else {
            nAkhir = nPg;
          }

          Database.table('hasil_ujians').where('id', '=', hasil.id).update({
            nilai_essay: nEssay,
            nilai_akhir: parseFloat(nAkhir.toFixed(2)),
            status: 'dinilai',
            updated_at: now
          });
        }
      }

      return ResponseFormat.success(null, "Nilai essay berhasil disimpan!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Finalisasi & Push Nilai Ulangan ke Rekap Nilai Utama
   */
  finalisasiUjian: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var ujian = Database.table('ujians').find(id);
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allSoals = this._safeGet('soals').filter(function(s) { return s.ujian_id == id; });
      var hasEssay = allSoals.filter(function(s) { return s.tipe === 'essay'; }).length > 0;

      var allHasil = this._safeGet('hasil_ujians');
      var hasilUjians = allHasil.filter(function(h) { return h.ujian_id == id; });

      if (hasEssay) {
        var belumDinilai = hasilUjians.filter(function(h) { return h.status === 'selesai'; }).length;
        if (belumDinilai > 0) {
          return ResponseFormat.error("Masih ada " + belumDinilai + " siswa yang belum dikoreksi essay-nya. Selesaikan koreksi terlebih dahulu.");
        }
      } else {
        var now = new Date().toISOString();
        hasilUjians.filter(function(h) { return h.status === 'selesai'; }).forEach(function(h) {
          try {
            Database.table('hasil_ujians').where('id', '=', h.id).update({
              status: 'dinilai',
              nilai_akhir: h.nilai_pg,
              updated_at: now
            });
          } catch(e){}
        });
      }

      var existingNilais = this._safeGet('nilais');
      var nowIso = new Date().toISOString();
      var savedCount = 0;

      var finishedHasil = this._safeGet('hasil_ujians').filter(function(h) {
        return h.ujian_id == id && (h.status === 'dinilai' || h.status === 'selesai');
      });

      finishedHasil.forEach(function(hasil) {
        var nScore = parseFloat(hasil.nilai_akhir !== undefined ? hasil.nilai_akhir : (hasil.nilai_pg || 0));
        var existing = existingNilais.find(function(n) {
          return n.siswa_id == hasil.siswa_id && String(n.bab).trim().toLowerCase() === String(ujian.bab).trim().toLowerCase();
        });

        if (existing) {
          Database.table('nilais').where('id', '=', existing.id).update({
            ulangan: nScore,
            updated_at: nowIso
          });
        } else {
          Database.table('nilais').insert({
            siswa_id: hasil.siswa_id,
            bab: ujian.bab,
            tugas: 0,
            quiz: 0,
            proyek: 0,
            ulangan: nScore,
            nilai_akhir: nScore,
            created_at: nowIso,
            updated_at: nowIso
          });
        }
        savedCount++;
      });

      return ResponseFormat.success({
        saved_count: savedCount
      }, "Nilai ujian berhasil difinalisasi! " + savedCount + " nilai ulangan disinkronkan ke rekap nilai.");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  // ==========================================
  // HASIL UJIAN & INSPEKSI LEMBAR JAWABAN
  // ==========================================

  /**
   * Daftar ujian untuk modul Hasil Ujian
   */
  getHasilUjianList: function(token, statusFilter) {
    try {
      SessionManager.requireRole(token, 'guru');
      return this.getUjianInit(token, statusFilter);
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Rekap hasil ujian per rombel kelas
   */
  getHasilUjianDetail: function(token, id, kelasId) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var allUjians = this._safeGet('ujians');
      var ujian = allUjians.find(function(u) { return u.id == id; });
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allSiswas = this._safeGet('siswas');
      var allUsers = this._safeGet('users');
      var allKelas = this._safeGet('kelas');
      var userMap = {};
      allUsers.forEach(function(u) { userMap[u.id] = u; });
      var kelasMap = {};
      allKelas.forEach(function(k) { kelasMap[k.id] = k; });
      var siswaMap = {};
      allSiswas.forEach(function(s) {
        siswaMap[s.id] = {
          id: s.id,
          nis: s.nis || '',
          nama: (userMap[s.user_id] ? userMap[s.user_id].name : s.nama) || 'Siswa',
          kelas_id: s.kelas_id,
          nama_kelas: (kelasMap[s.kelas_id] ? kelasMap[s.kelas_id].nama_kelas : '-')
        };
      });

      var allSettings = this._safeGet('settings');
      var kkmSetting = allSettings.find(function(st) { return st.key === 'kkm_nilai'; });
      var kkm = kkmSetting ? (parseFloat(kkmSetting.value) || 75) : 75;

      var allSoals = this._safeGet('soals');
      var hasEssay = allSoals.filter(function(s) { return s.ujian_id == id && s.tipe === 'essay'; }).length > 0;

      var allUjianKelas = this._safeGet('ujian_kelas');
      var targetKelasIds = allUjianKelas.filter(function(uk) { return uk.ujian_id == id; }).map(function(uk) { return uk.kelas_id; });
      var targetKelasList = allKelas.filter(function(k) {
        return targetKelasIds.indexOf(k.id) !== -1 || targetKelasIds.indexOf(String(k.id)) !== -1;
      }).map(function(k) {
        return { id: k.id, nama_kelas: String(k.nama_kelas || '') };
      });
      if (targetKelasList.length === 0) {
        targetKelasList = allKelas.map(function(k) { return { id: k.id, nama_kelas: String(k.nama_kelas || '') }; });
      }

      var allHasil = this._safeGet('hasil_ujians');
      var hasilUjians = allHasil.filter(function(h) { return h.ujian_id == id; });

      if (kelasId) {
        hasilUjians = hasilUjians.filter(function(h) {
          var s = siswaMap[h.siswa_id];
          return s && (s.kelas_id == kelasId);
        });
      }

      var rows = hasilUjians.map(function(h) {
        var sInfo = siswaMap[h.siswa_id] || { nis: '', nama: 'Siswa', nama_kelas: '-' };
        var nAkhir = parseFloat(h.nilai_akhir !== undefined ? h.nilai_akhir : (h.nilai_pg || 0));
        return {
          id: h.id,
          siswa_id: h.siswa_id,
          nis: String(sInfo.nis || ''),
          nama_siswa: String(sInfo.nama || 'Siswa'),
          nama_kelas: String(sInfo.nama_kelas || '-'),
          nilai_pg: parseFloat(h.nilai_pg || 0),
          nilai_essay: parseFloat(h.nilai_essay || 0),
          nilai_akhir: nAkhir,
          is_lulus: nAkhir >= kkm,
          tab_switch_count: parseInt(h.tab_switch_count) || 0,
          status: String(h.status || 'selesai'),
          created_at: UjianService._formatIsoStr(h.created_at)
        };
      });

      return ResponseFormat.success({
        ujian: {
          id: ujian.id,
          judul: String(ujian.judul || ''),
          bab: String(ujian.bab || ''),
          status: String(ujian.status || ''),
          tanggal: UjianService._formatDateStr(ujian.tanggal),
          durasi: parseInt(ujian.durasi) || 60
        },
        kkm: kkm,
        has_essay: hasEssay,
        kelasList: targetKelasList,
        selected_kelas: kelasId || '',
        hasil: rows
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir. Silakan login kembali.');
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Detail lembar pengerjaan siswa per butir soal
   */
  getDetailJawabanSiswa: function(token, id, siswaId) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var allUjians = this._safeGet('ujians');
      var ujian = allUjians.find(function(u) { return u.id == id; });
      if (!ujian) return ResponseFormat.error("Ujian tidak ditemukan.");

      var allSiswas = this._safeGet('siswas');
      var siswa = allSiswas.find(function(s) { return s.id == siswaId; });
      if (!siswa) return ResponseFormat.error("Siswa tidak ditemukan.");

      var allUsers = this._safeGet('users');
      var user = allUsers.find(function(u) { return u.id == siswa.user_id; }) || {};
      var allKelas = this._safeGet('kelas');
      var kelas = allKelas.find(function(k) { return k.id == siswa.kelas_id; }) || {};

      var allHasil = this._safeGet('hasil_ujians');
      var hasil = allHasil.find(function(h) { return h.ujian_id == id && h.siswa_id == siswaId; });

      if (!hasil) {
        return ResponseFormat.error("Siswa belum memulai ujian ini.");
      }

      var allSoals = this._safeGet('soals');
      var soals = allSoals.filter(function(s) { return s.ujian_id == id; });
      soals.sort(function(a, b) { return (parseInt(a.urutan) || 0) - (parseInt(b.urutan) || 0); });

      var allJawabans = this._safeGet('jawaban_siswas');
      var jawabans = allJawabans.filter(function(j) { return j.ujian_id == id && j.siswa_id == siswaId; });

      var jwbMap = {};
      jawabans.forEach(function(j) {
        jwbMap[j.soal_id] = j;
      });

      var allSettings = this._safeGet('settings');
      var kkmSetting = allSettings.find(function(st) { return st.key === 'kkm_nilai'; });
      var kkm = kkmSetting ? (parseFloat(kkmSetting.value) || 75) : 75;

      var allLogs = this._safeGet('log_ujians');
      var logs = allLogs.filter(function(l) { return l.ujian_id == id && l.siswa_id == siswaId; });
      logs.sort(function(a, b) { return new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime(); });

      var items = soals.map(function(s, idx) {
        var jwb = jwbMap[s.id];
        return {
          no: idx + 1,
          id: s.id,
          tipe: String(s.tipe || 'pg'),
          pertanyaan: String(s.pertanyaan || ''),
          opsi_a: String(s.opsi_a || ''),
          opsi_b: String(s.opsi_b || ''),
          opsi_c: String(s.opsi_c || ''),
          opsi_d: String(s.opsi_d || ''),
          jawaban_benar: String(s.jawaban_benar || ''),
          bobot: parseInt(s.bobot) || 1,
          jawaban_siswa: String(jwb ? (jwb.jawaban || '') : ''),
          is_correct: jwb ? (jwb.is_correct === true || jwb.is_correct === 'true' || jwb.is_correct === 1) : false
        };
      });

      return ResponseFormat.success({
        ujian: {
          id: ujian.id,
          judul: String(ujian.judul || ''),
          bab: String(ujian.bab || '')
        },
        siswa: {
          id: siswa.id,
          nis: String(siswa.nis || ''),
          nama: String(user.name || siswa.nama || 'Siswa'),
          nama_kelas: String(kelas.nama_kelas || '-')
        },
        hasil: {
          id: hasil.id,
          nilai_pg: parseFloat(hasil.nilai_pg || 0),
          nilai_essay: parseFloat(hasil.nilai_essay || 0),
          nilai_akhir: parseFloat(hasil.nilai_akhir !== undefined ? hasil.nilai_akhir : (hasil.nilai_pg || 0)),
          status: String(hasil.status || 'selesai')
        },
        kkm: kkm,
        soals: items,
        logs: logs.map(function(l) {
          return {
            id: l.id,
            event: String(l.event || ''),
            detail: String(l.detail || ''),
            created_at: UjianService._formatIsoStr(l.created_at)
          };
        })
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir. Silakan login kembali.');
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Override / Penilaian manual individual per lembar ujian siswa
   */
  updateNilaiSiswaIndividu: function(token, id, siswaId, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureUjianColumns();

      var hasil = Database.table('hasil_ujians')
        .where('ujian_id', '=', id)
        .where('siswa_id', '=', siswaId)
        .first();

      if (!hasil) return ResponseFormat.error("Lembar ujian siswa tidak ditemukan.");

      var nilaiPg = parseFloat(payload.nilai_pg !== undefined ? payload.nilai_pg : hasil.nilai_pg);
      var nilaiEssay = parseFloat(payload.nilai_essay !== undefined ? payload.nilai_essay : (hasil.nilai_essay || 0));
      var nilaiAkhir = parseFloat(payload.nilai_akhir !== undefined ? payload.nilai_akhir : (payload.calculated_nilai_akhir || nilaiPg));

      var now = new Date().toISOString();
      Database.table('hasil_ujians').where('id', '=', hasil.id).update({
        nilai_pg: parseFloat(nilaiPg.toFixed(2)),
        nilai_essay: parseFloat(nilaiEssay.toFixed(2)),
        nilai_akhir: parseFloat(nilaiAkhir.toFixed(2)),
        status: 'dinilai',
        updated_at: now
      });

      return ResponseFormat.success(null, "Nilai lembar ujian siswa berhasil diperbarui!");
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

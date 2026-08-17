/**
 * SiswaService.gs
 * Backend service untuk Portal & Dashboard Siswa (Fase 7)
 * Migrasi dari SiswaController.php & UjianSiswaController.php
 */

const SiswaService = {

  /**
   * Helper safe query table
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
   * Helper normalisasi tanggal ke string YYYY-MM-DD
   */
  _formatDate: function(val) {
    if (!val) return '';
    if (val instanceof Date) {
      return Utilities.formatDate(val, Session.getScriptTimeZone() || "GMT+7", "yyyy-MM-dd");
    }
    var str = String(val).trim();
    if (str.indexOf('T') !== -1) return str.split('T')[0];
    return str;
  },

  /**
   * Helper mencari data siswa berdasarkan user ID atau username/NIS
   */
  _findSiswaByUser: function(user, allSiswas) {
    if (!user) return null;
    if (!allSiswas) allSiswas = this._safeGet('siswas');
    
    // 1. Cari berdasarkan user_id
    var siswa = allSiswas.find(function(s) {
      return s.user_id == user.id || String(s.user_id) == String(user.id);
    });

    // 2. Fallback: Cari berdasarkan NIS == username
    if (!siswa && user.username) {
      var uName = String(user.username).trim().toLowerCase();
      siswa = allSiswas.find(function(s) {
        return String(s.nis || '').trim().toLowerCase() === uName;
      });
    }

    return siswa || null;
  },

  /**
   * Mengambil data lengkap Dashboard Siswa (Rapor, Kehadiran, Keaktifan, Materi, Ujian)
   */
  getDashboard: function(token) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      
      var allSiswas = this._safeGet('siswas');
      var siswa = this._findSiswaByUser(user, allSiswas);
      
      var namaKelas = '-';
      var kelasId = null;
      if (siswa && siswa.kelas_id) {
        kelasId = siswa.kelas_id;
        var allKelas = this._safeGet('kelas');
        var k = allKelas.find(function(item) {
          return item.id == siswa.kelas_id || String(item.id) == String(siswa.kelas_id);
        });
        if (k) namaKelas = k.nama_kelas || '-';
      }

      // 1. Rekap Presensi Siswa
      var allAbsensis = this._safeGet('absensis');
      var absensis = siswa ? allAbsensis.filter(function(a) {
        return a.siswa_id == siswa.id || String(a.siswa_id) == String(siswa.id);
      }) : [];

      var hadir = 0, sakit = 0, izin = 0, dispen = 0, alpha = 0;
      absensis.forEach(function(a) {
        var st = String(a.status || '').toLowerCase().trim();
        if (st === 'hadir') hadir++;
        else if (st === 'sakit') sakit++;
        else if (st === 'izin') izin++;
        else if (st === 'dispen') dispen++;
        else if (st === 'alpha' || st === 'alpa') alpha++;
      });
      var totalAbsen = absensis.length;
      var persentaseHadir = totalAbsen > 0 ? (((hadir + dispen) / totalAbsen) * 100) : 0;

      // 2. Keaktifan Harian (Penilaian Harian)
      var allPH = this._safeGet('penilaian_harians');
      var myPH = siswa ? allPH.filter(function(p) {
        return p.siswa_id == siswa.id || String(p.siswa_id) == String(siswa.id);
      }) : [];
      var totalSkorPH = 0;
      myPH.forEach(function(p) {
        totalSkorPH += (parseFloat(p.nilai) || 0);
      });
      var rataKeaktifan = myPH.length > 0 ? (totalSkorPH / myPH.length) : 80;

      // 3. Daftar Nilai Rapor Per Bab
      var allNilais = this._safeGet('nilais');
      var nilais = siswa ? allNilais.filter(function(n) {
        return n.siswa_id == siswa.id || String(n.siswa_id) == String(siswa.id);
      }) : [];
      var totalNilaiAkhir = 0;
      nilais.forEach(function(n) {
        totalNilaiAkhir += (parseFloat(n.nilai_akhir) || 0);
      });
      var rataRataNilai = nilais.length > 0 ? (totalNilaiAkhir / nilais.length) : 0;

      // 4. Pengaturan KKM
      var allSettings = this._safeGet('settings');
      var kkmSetting = allSettings.find(function(st) { return st.key === 'kkm_nilai'; });
      var kkm = kkmSetting ? (parseFloat(kkmSetting.value) || 75) : 75;

      // 5. Materi Belajar
      var allMateris = this._safeGet('materis');
      allMateris.sort(function(a, b) {
        return new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime();
      });
      var formattedMateris = allMateris.map(function(m) {
        return {
          id: m.id,
          judul: String(m.judul || ''),
          deskripsi: String(m.deskripsi || ''),
          foto: String(m.foto || ''),
          file_materi: String(m.file_materi || ''),
          link: String(m.link || ''),
          created_at: SiswaService._formatDate(m.created_at)
        };
      });

      // 6. Ujian CBT Aktif Kelas Siswa
      var ujianAktif = [];
      if (kelasId) {
        var allUjians = this._safeGet('ujians');
        var allUjianKelas = this._safeGet('ujian_kelas');
        var allHasil = this._safeGet('hasil_ujians');

        var myHasilMap = {};
        if (siswa) {
          allHasil.filter(function(h) {
            return h.siswa_id == siswa.id || String(h.siswa_id) == String(siswa.id);
          }).forEach(function(h) {
            myHasilMap[h.ujian_id] = h;
          });
        }

        var myKelasUjianIds = allUjianKelas.filter(function(uk) {
          return uk.kelas_id == kelasId || String(uk.kelas_id) == String(kelasId);
        }).map(function(uk) { return uk.ujian_id; });

        ujianAktif = allUjians.filter(function(u) {
          return u.status === 'aktif' && (myKelasUjianIds.indexOf(u.id) !== -1 || myKelasUjianIds.indexOf(String(u.id)) !== -1);
        }).map(function(u) {
          var myHasil = myHasilMap[u.id];
          return {
            id: u.id,
            judul: String(u.judul || ''),
            bab: String(u.bab || ''),
            durasi: parseInt(u.durasi) || 60,
            sudah_mengerjakan: myHasil ? (myHasil.status === 'selesai' || myHasil.status === 'dinilai') : false,
            sedang_mengerjakan: myHasil ? (myHasil.status === 'mengerjakan') : false,
            nilai_akhir: myHasil ? parseFloat(myHasil.nilai_akhir !== undefined ? myHasil.nilai_akhir : (myHasil.nilai_pg || 0)) : null
          };
        });
      }

      return ResponseFormat.success({
        siswa: {
          id: siswa ? siswa.id : null,
          nis: siswa ? String(siswa.nis || '') : '',
          nama: String(user.name || ''),
          username: String(user.username || ''),
          nama_kelas: namaKelas
        },
        rekap_absensi: {
          total: totalAbsen,
          hadir: hadir,
          sakit: sakit,
          izin: izin,
          dispen: dispen,
          alpha: alpha,
          persentase: parseFloat(persentaseHadir.toFixed(1))
        },
        rekap_keaktifan: {
          rata_rata: parseFloat(rataKeaktifan.toFixed(1))
        },
        rekap_nilai: {
          rata_rata: parseFloat(rataRataNilai.toFixed(1)),
          is_tuntas: rataRataNilai >= kkm,
          kkm: kkm,
          daftar: nilais.map(function(n) {
            return {
              id: n.id,
              bab: String(n.bab || ''),
              tugas: parseFloat(n.tugas || 0),
              quiz: parseFloat(n.quiz || 0),
              proyek: parseFloat(n.proyek || 0),
              ulangan: parseFloat(n.ulangan || 0),
              nilai_akhir: parseFloat(n.nilai_akhir || 0),
              is_lulus: parseFloat(n.nilai_akhir || 0) >= kkm
            };
          })
        },
        materis: formattedMateris,
        ujian_aktif: ujianAktif
      }, 'Data dashboard siswa berhasil diambil');
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized('Sesi login telah berakhir');
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengubah profil siswa (Username / Password)
   */
  updateProfil: function(token, payload) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      if (!payload || !payload.username) {
        return ResponseFormat.error('Username tidak boleh kosong.');
      }

      var newUsername = String(payload.username).trim().toLowerCase();
      
      // Check unique username
      var allUsers = this._safeGet('users');
      var duplicate = allUsers.find(function(u) {
        return u.id != user.id && String(u.username || '').trim().toLowerCase() === newUsername;
      });
      if (duplicate) {
        return ResponseFormat.error('Username sudah digunakan oleh pengguna lain.');
      }

      var updateData = {
        username: newUsername,
        updated_at: new Date().toISOString()
      };

      // Password baru jika diisi
      if (payload.password && String(payload.password).trim() !== '') {
        if (String(payload.password).length < 4) {
          return ResponseFormat.error('Password baru minimal 4 karakter.');
        }
        updateData.password = Security.hashPassword(String(payload.password).trim());
      }

      Database.table('users').where('id', '=', user.id).update(updateData);

      return ResponseFormat.success(null, 'Profil dan kata sandi berhasil diperbarui!');
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengirim testimoni siswa (Rate limit 1x per 7 hari)
   */
  storeKomentar: function(token, payload) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      
      var allSiswas = this._safeGet('siswas');
      var siswa = this._findSiswaByUser(user, allSiswas);
      if (!siswa) return ResponseFormat.error('Data siswa tidak ditemukan.');

      if (!payload || !payload.isi_komentar || String(payload.isi_komentar).trim() === '') {
        return ResponseFormat.error('Isi testimoni tidak boleh kosong.');
      }

      var text = String(payload.isi_komentar).trim();
      if (text.length > 300) {
        return ResponseFormat.error('Isi testimoni maksimal 300 karakter.');
      }

      // Check 7 days limit
      var allKomentars = this._safeGet('komentars');
      var myKomentars = allKomentars.filter(function(k) {
        return k.siswa_id == siswa.id || String(k.siswa_id) == String(siswa.id);
      });
      if (myKomentars.length > 0) {
        myKomentars.sort(function(a, b) {
          return new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime();
        });
        var lastCreated = new Date(myKomentars[0].created_at || 0).getTime();
        var now = new Date().getTime();
        var diffDays = (now - lastCreated) / (1000 * 60 * 60 * 24);
        if (diffDays < 7) {
          return ResponseFormat.error('Anda hanya dapat mengirim testimoni 1 kali dalam 7 hari.');
        }
      }

      var nowIso = new Date().toISOString();
      Database.table('komentars').insert({
        siswa_id: siswa.id,
        user_name: user.name,
        isi_komentar: text,
        is_anonim: payload.is_anonim === true || payload.is_anonim === '1' || payload.is_anonim === 1,
        created_at: nowIso,
        updated_at: nowIso
      });

      return ResponseFormat.success(null, 'Testimoni Anda berhasil dikirim dan akan tampil di halaman depan!');
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengambil riwayat detail kehadiran siswa (Buku Presensi Digital)
   */
  getKehadiranSaya: function(token) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      
      var allSiswas = this._safeGet('siswas');
      var siswa = this._findSiswaByUser(user, allSiswas);
      
      var namaKelas = '-';
      var kelasId = null;
      if (siswa && siswa.kelas_id) {
        kelasId = siswa.kelas_id;
        var allKelas = this._safeGet('kelas');
        var k = allKelas.find(function(item) {
          return item.id == siswa.kelas_id || String(item.id) == String(siswa.kelas_id);
        });
        if (k) namaKelas = k.nama_kelas || '-';
      }

      var allAbsensis = this._safeGet('absensis');
      var absensis = siswa ? allAbsensis.filter(function(a) {
        return a.siswa_id == siswa.id || String(a.siswa_id) == String(siswa.id);
      }) : [];

      var hadir = 0, sakit = 0, izin = 0, dispen = 0, alpa = 0;
      absensis.forEach(function(a) {
        var st = String(a.status || '').toLowerCase().trim();
        if (st === 'hadir') hadir++;
        else if (st === 'sakit') sakit++;
        else if (st === 'izin') izin++;
        else if (st === 'dispen') dispen++;
        else if (st === 'alpha' || st === 'alpa') alpa++;
      });

      absensis.sort(function(a, b) {
        var dateA = new Date(a.tanggal || 0).getTime();
        var dateB = new Date(b.tanggal || 0).getTime();
        return dateB - dateA;
      });

      var allPH = this._safeGet('penilaian_harians');
      var phMap = {};
      if (siswa) {
        allPH.filter(function(p) {
          return p.siswa_id == siswa.id || String(p.siswa_id) == String(siswa.id);
        }).forEach(function(p) {
          var tgl = SiswaService._formatDate(p.tanggal);
          phMap[tgl] = {
            pertemuan: p.pertemuan || '-',
            nilai: p.nilai || 0,
            catatan: p.catatan || ''
          };
        });
      }

      var formattedRows = absensis.map(function(a, idx) {
        var tglStr = SiswaService._formatDate(a.tanggal);
        var ph = phMap[tglStr] || { pertemuan: '-', nilai: '-', catatan: '-' };
        return {
          no: idx + 1,
          id: a.id,
          tanggal: tglStr,
          pertemuan: String(ph.pertemuan || '-'),
          status: String(a.status || 'hadir').toLowerCase().trim(),
          catatan: String(a.catatan || ph.catatan || '-'),
          nilai_keaktifan: ph.nilai
        };
      });

      return ResponseFormat.success({
        siswa: {
          id: siswa ? siswa.id : null,
          nis: siswa ? String(siswa.nis || '') : '',
          nama: String(user.name || ''),
          nama_kelas: namaKelas
        },
        rekap: {
          hadir: hadir,
          sakit: sakit,
          izin: izin,
          dispen: dispen,
          alpa: alpa,
          total: absensis.length
        },
        riwayat: formattedRows
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengambil daftar ujian online untuk portal CBT siswa
   */
  getUjianSiswaList: function(token) {
    try {
      const user = SessionManager.requireRole(token, 'siswa');
      
      var allSiswas = this._safeGet('siswas');
      var siswa = this._findSiswaByUser(user, allSiswas);
      if (!siswa || !siswa.kelas_id) {
        return ResponseFormat.success({ ujians: [] });
      }

      var allUjians = this._safeGet('ujians');
      var allUjianKelas = this._safeGet('ujian_kelas');
      var allHasil = this._safeGet('hasil_ujians');

      var myKelasUjianIds = allUjianKelas.filter(function(uk) {
        return uk.kelas_id == siswa.kelas_id || String(uk.kelas_id) == String(siswa.kelas_id);
      }).map(function(uk) { return uk.ujian_id; });

      var myHasilMap = {};
      allHasil.filter(function(h) {
        return h.siswa_id == siswa.id || String(h.siswa_id) == String(siswa.id);
      }).forEach(function(h) {
        myHasilMap[h.ujian_id] = h;
      });

      var activeExams = allUjians.filter(function(u) {
        return (myKelasUjianIds.indexOf(u.id) !== -1 || myKelasUjianIds.indexOf(String(u.id)) !== -1) && (u.status === 'aktif' || myHasilMap[u.id]);
      }).map(function(u) {
        var myHasil = myHasilMap[u.id];
        return {
          id: u.id,
          judul: String(u.judul || ''),
          bab: String(u.bab || ''),
          tanggal: SiswaService._formatDate(u.tanggal),
          durasi: parseInt(u.durasi) || 60,
          status_ujian: String(u.status || 'draft'),
          status_pengerjaan: myHasil ? String(myHasil.status || '') : 'belum',
          nilai_akhir: myHasil ? (parseFloat(myHasil.nilai_akhir !== undefined ? myHasil.nilai_akhir : (myHasil.nilai_pg || 0))) : null,
          started_at: myHasil ? myHasil.started_at : null
        };
      });

      return ResponseFormat.success({
        ujians: activeExams
      });
    } catch(e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

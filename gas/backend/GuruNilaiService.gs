/**
 * GuruNilaiService.gs
 * Melayani Data Nilai Siswa (Fase 6F)
 */
var GuruNilaiService = {

    /**
     * getNilaiInit
     * Mengambil daftar kelas, bab, dan setting kkm.
     */
    getNilaiInit: function(token) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }

        // Ambil kelas
        var kelas = Database.table('kelas').get();
        
        // Ambil bab unik dari nilais (riwayat)
        var nilais = Database.table('nilais').get() || [];
        var babSet = {};
        nilais.forEach(function(n) {
            if (n.bab) babSet[n.bab] = true;
        });

        // Ambil bab unik dari ujians (jika ada)
        try {
            var ujians = Database.table('ujians').get() || [];
            ujians.forEach(function(u) {
                if (u.bab) babSet[u.bab] = true;
            });
        } catch (e) {
            // Abaikan jika tabel ujians belum ada
        }

        var daftar_bab = Object.keys(babSet).sort();

        // Ambil KKM
        var kkm = 75;
        try {
            var kkmSetting = Database.table('settings').where('key', '=', 'kkm_nilai').first();
            if (kkmSetting) kkm = parseFloat(kkmSetting.value) || 75;
        } catch (e) {
            // Tabel settings mungkin belum di-seed
        }

        return {
            status: 'success',
            data: {
                kelas: kelas,
                daftar_bab: daftar_bab,
                kkm: kkm
            }
        };
    },

    /**
     * getNilaiData
     * Mengambil daftar siswa di kelas tertentu, beserta rata-rata harian dan riwayat nilai kelas tersebut.
     */
    getNilaiData: function(token, kelas_id) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }

        if (!kelas_id) {
            return {
                status: 'success',
                data: {
                    siswas: [],
                    riwayat_nilai: []
                }
            };
        }

        // Ambil siswa berdasarkan kelas_id
        var siswas = Database.table('siswas').where('kelas_id', '=', kelas_id).get();
        
        // Populate user data
        var users = Database.table('users').get();
        var userMap = {};
        users.forEach(function(u) { userMap[u.id] = u; });
        
        // Populate rata_harian
        var penilaian_harians = Database.table('penilaian_harians').get() || [];
        var harianMap = {}; // siswa_id -> { total: 0, count: 0 }
        
        penilaian_harians.forEach(function(ph) {
            if (!harianMap[ph.siswa_id]) {
                harianMap[ph.siswa_id] = { total: 0, count: 0 };
            }
            harianMap[ph.siswa_id].total += parseFloat(ph.nilai || 0);
            harianMap[ph.siswa_id].count += 1;
        });

        var siswaIds = {};
        siswas.forEach(function(s) {
            s.user = userMap[s.user_id] || {};
            siswaIds[s.id] = true;
            
            // Hitung rata_harian
            if (harianMap[s.id] && harianMap[s.id].count > 0) {
                s.rata_harian = (harianMap[s.id].total / harianMap[s.id].count).toFixed(1);
            } else {
                s.rata_harian = 80.0; // Default fallback
            }
        });

        // Ambil riwayat nilai khusus kelas ini
        var nilais = Database.table('nilais').get() || [];
        var riwayat_nilai = nilais.filter(function(n) {
            return siswaIds[n.siswa_id] === true;
        });
        
        var kelasObj = Database.table('kelas').find(kelas_id) || {};
        
        // Populate relasi untuk riwayat
        riwayat_nilai.forEach(function(rn) {
            var s = siswas.find(function(si) { return si.id == rn.siswa_id; });
            rn.siswa = s ? { id: s.id, nis: s.nis, user: s.user, kelas: kelasObj } : {};
        });

        // Urutkan berdasarkan waktu input terbaru
        riwayat_nilai.sort(function(a, b) {
            var dateA = new Date(a.created_at || 0);
            var dateB = new Date(b.created_at || 0);
            return dateB - dateA; // Descending
        });

        return {
            status: 'success',
            data: {
                siswas: siswas,
                riwayat_nilai: riwayat_nilai
            }
        };
    },

    /**
     * getRataUjian
     * Mengambil rata-rata nilai ujian siswa berdasarkan bab (Fallback ke nilai ulangan sebelumnya)
     */
    getRataUjian: function(token, bab) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }
        
        bab = (bab || '').trim().toLowerCase();
        
        var rata_ujian = {}; // siswa_id -> nilai rata-rata
        
        // Coba dari hasil_ujians
        try {
            var ujians = Database.table('ujians').get() || [];
            var hasil_ujians = Database.table('hasil_ujians').get() || [];
            
            // Filter ujians yg cocok dengan bab
            var ujianIds = {};
            if (bab !== '') {
                ujians.forEach(function(u) {
                    var uBab = (u.bab || '').toLowerCase();
                    var uJudul = (u.judul || '').toLowerCase();
                    if (uBab === bab || uBab.indexOf(bab) > -1 || uJudul.indexOf(bab) > -1) {
                        ujianIds[u.id] = true;
                    }
                });
            }
            
            var mapSiswa = {};
            hasil_ujians.forEach(function(hu) {
                if (['selesai', 'dinilai', 'mengerjakan'].indexOf(hu.status) > -1) {
                    // Jika bab spesifik diberikan, ujian harus cocok
                    if (bab !== '' && !ujianIds[hu.ujian_id]) {
                        return; // Skip
                    }
                    if (!mapSiswa[hu.siswa_id]) mapSiswa[hu.siswa_id] = { total: 0, count: 0 };
                    mapSiswa[hu.siswa_id].total += parseFloat(hu.nilai_akhir || 0);
                    mapSiswa[hu.siswa_id].count += 1;
                }
            });
            
            Object.keys(mapSiswa).forEach(function(sid) {
                rata_ujian[sid] = (mapSiswa[sid].total / mapSiswa[sid].count).toFixed(2);
            });
            
        } catch (e) {}

        // Fallback ke nilais (ambil ulangan sebelumnya)
        var nilais = Database.table('nilais').get() || [];
        nilais.forEach(function(n) {
            if (n.ulangan && parseFloat(n.ulangan) > 0) {
                var nBab = (n.bab || '').toLowerCase();
                if (bab === '' || nBab === bab || nBab.indexOf(bab) > -1) {
                    // Hanya isi jika belum ada di hasil ujian
                    if (!rata_ujian[n.siswa_id]) {
                        rata_ujian[n.siswa_id] = parseFloat(n.ulangan).toFixed(2);
                    }
                }
            }
        });

        return {
            status: 'success',
            data: rata_ujian
        };
    },

    /**
     * storeNilai
     * Proses penyimpanan massal (Upsert) berdasarkan siswa_id & bab
     */
    storeNilai: function(token, payload) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }

        var bab = payload.bab;
        var p_harian = parseFloat(payload.p_harian || 0);
        var p_tugas = parseFloat(payload.p_tugas || 0);
        var p_quiz = parseFloat(payload.p_quiz || 0);
        var p_proyek = parseFloat(payload.p_proyek || 0);
        var p_ulangan = parseFloat(payload.p_ulangan || 0);
        var sertakan_ulangan = payload.sertakan_ulangan === true || payload.sertakan_ulangan === '1';
        
        if (!sertakan_ulangan) {
            p_ulangan = 0;
        }

        var totalBobot = p_harian + p_tugas + p_quiz + p_proyek + p_ulangan;
        
        // Toleransi floating point
        if (Math.abs(totalBobot - 100) > 0.01) {
            return { status: 'error', message: 'Total persentase bobot harus tepat 100%' };
        }
        
        // Convert ke pengali (0.0 - 1.0)
        p_harian = p_harian / 100;
        p_tugas = p_tugas / 100;
        p_quiz = p_quiz / 100;
        p_proyek = p_proyek / 100;
        p_ulangan = p_ulangan / 100;

        var nilaiObj = payload.nilai || {};
        var siswaIds = Object.keys(nilaiObj);
        if (siswaIds.length === 0) {
            return { status: 'error', message: 'Tidak ada data siswa untuk disimpan' };
        }

        var now = new Date().toISOString();
        
        Database.transaction(function() {
            var existingNilais = Database.table('nilais').get() || [];
            
            siswaIds.forEach(function(siswa_id) {
                var d = nilaiObj[siswa_id];
                var harian = parseFloat(d.harian || 0);
                var tugas = parseFloat(d.tugas || 0);
                var quiz = parseFloat(d.quiz || 0);
                var proyek = parseFloat(d.proyek || 0);
                var ulangan = parseFloat(d.ulangan || 0);

                var nilai_akhir = (harian * p_harian) + (tugas * p_tugas) + (quiz * p_quiz) + (proyek * p_proyek);
                if (sertakan_ulangan) {
                    nilai_akhir += (ulangan * p_ulangan);
                }

                var dataToSave = {
                    siswa_id: siswa_id,
                    bab: bab,
                    tugas: tugas.toFixed(2),
                    quiz: quiz.toFixed(2),
                    proyek: proyek.toFixed(2),
                    ulangan: ulangan.toFixed(2),
                    nilai_akhir: nilai_akhir.toFixed(2),
                    updated_at: now
                };

                // Cari apakah sudah ada di db (siswa_id & bab)
                var existing = existingNilais.find(function(en) {
                    return en.siswa_id == siswa_id && String(en.bab).toLowerCase() === String(bab).toLowerCase();
                });

                if (existing) {
                    Database.table('nilais').where('id', '=', existing.id).update(dataToSave);
                } else {
                    dataToSave.created_at = now;
                    Database.table('nilais').insert(dataToSave);
                }
            });
        });

        return { status: 'success', message: 'Nilai berhasil disimpan!' };
    },

    /**
     * updateNilai
     * Update spesifik satu baris nilai (digunakan di modal Riwayat)
     */
    updateNilai: function(token, id, payload) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }

        var record = Database.table('nilais').find(id);
        if (!record) {
            return { status: 'error', message: 'Data nilai tidak ditemukan' };
        }

        var bab = payload.bab;
        var p_harian = parseFloat(payload.p_harian || 0);
        var p_tugas = parseFloat(payload.p_tugas || 0);
        var p_quiz = parseFloat(payload.p_quiz || 0);
        var p_proyek = parseFloat(payload.p_proyek || 0);
        var p_ulangan = parseFloat(payload.p_ulangan || 0);
        var sertakan_ulangan = payload.sertakan_ulangan === true || payload.sertakan_ulangan === '1';
        
        if (!sertakan_ulangan) {
            p_ulangan = 0;
        }

        var totalBobot = p_harian + p_tugas + p_quiz + p_proyek + p_ulangan;
        
        if (Math.abs(totalBobot - 100) > 0.01) {
            return { status: 'error', message: 'Total persentase bobot harus tepat 100%' };
        }
        
        p_harian = p_harian / 100;
        p_tugas = p_tugas / 100;
        p_quiz = p_quiz / 100;
        p_proyek = p_proyek / 100;
        p_ulangan = p_ulangan / 100;

        var harian = parseFloat(payload.harian || 0);
        var tugas = parseFloat(payload.tugas || 0);
        var quiz = parseFloat(payload.quiz || 0);
        var proyek = parseFloat(payload.proyek || 0);
        var ulangan = parseFloat(payload.ulangan || 0);

        var nilai_akhir = (harian * p_harian) + (tugas * p_tugas) + (quiz * p_quiz) + (proyek * p_proyek);
        if (sertakan_ulangan) {
            nilai_akhir += (ulangan * p_ulangan);
        }

        var dataToSave = {
            bab: bab,
            tugas: tugas.toFixed(2),
            quiz: quiz.toFixed(2),
            proyek: proyek.toFixed(2),
            ulangan: ulangan.toFixed(2),
            nilai_akhir: nilai_akhir.toFixed(2),
            updated_at: new Date().toISOString()
        };

        Database.table('nilais').where('id', '=', id).update(dataToSave);
        return { status: 'success', message: 'Data nilai berhasil diperbarui!' };
    },

    destroyNilai: function(token, id) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }
        
        Database.table('nilais').where('id', '=', id).delete();
        return { status: 'success', message: 'Nilai berhasil dihapus.' };
    },

    destroyNilaiByBab: function(token, bab) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }
        
        var nilais = Database.table('nilais').where('bab', '=', bab).get();
        Database.transaction(function() {
            nilais.forEach(function(n) {
                Database.table('nilais').where('id', '=', n.id).delete();
            });
        });
        
        return { status: 'success', message: 'Seluruh nilai pada bab ini berhasil dihapus.' };
    },

    updateKkm: function(token, kkmVal) {
        var user = SessionManager.getUser(token);
        if (!user || user.role !== 'guru') {
            return { status: 'error', message: 'Unauthorized' };
        }
        
        var kkm = parseFloat(kkmVal);
        if (isNaN(kkm) || kkm < 0 || kkm > 100) {
            return { status: 'error', message: 'Nilai KKM tidak valid' };
        }

        var existing = Database.table('settings').where('key', '=', 'kkm_nilai').first();
        if (existing) {
            Database.table('settings').where('id', '=', existing.id).update({
                value: String(kkm),
                updated_at: new Date().toISOString()
            });
        } else {
            Database.table('settings').insert({
                key: 'kkm_nilai',
                value: String(kkm),
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString()
            });
        }

        return { status: 'success', message: 'KKM berhasil diperbarui.' };
    }
};

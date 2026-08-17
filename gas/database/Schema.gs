/**
 * Schema.gs
 * Definisi skema tabel untuk Google Sheets menyerupai skema database Laravel.
 * Berisi konfigurasi kolom, primary key, foreign key, nullability, defaults, dan unique constraints.
 */

const DB_SCHEMA = {
  users: {
    table: 'users',
    primaryKey: 'id',
    columns: ['id', 'name', 'username', 'role', 'password', 'avatar', 'remember_token', 'created_at', 'updated_at'],
    constraints: {
      unique: ['username'],
      enums: { role: ['guru', 'siswa', 'guest'] }
    },
    defaults: { role: 'siswa' }
  },
  kelas: {
    table: 'kelas',
    primaryKey: 'id',
    columns: ['id', 'nama_kelas', 'wali_kelas', 'created_at', 'updated_at'],
    constraints: {
      unique: ['nama_kelas']
    }
  },
  siswas: {
    table: 'siswas',
    primaryKey: 'id',
    columns: ['id', 'user_id', 'kelas_id', 'nis', 'nisn', 'nama', 'jk', 'created_at', 'updated_at'],
    constraints: {
      unique: ['nis', 'user_id'],
      foreignKeys: {
        user_id: { table: 'users', column: 'id', onDelete: 'cascade' },
        kelas_id: { table: 'kelas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  absensis: {
    table: 'absensis',
    primaryKey: 'id',
    columns: ['id', 'siswa_id', 'kelas_id', 'tanggal', 'status', 'dispen', 'created_at', 'updated_at'],
    constraints: {
      unique_composite: [['siswa_id', 'tanggal']],
      foreignKeys: {
        siswa_id: { table: 'siswas', column: 'id', onDelete: 'cascade' },
        kelas_id: { table: 'kelas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  nilais: {
    table: 'nilais',
    primaryKey: 'id',
    columns: ['id', 'siswa_id', 'bab', 'tugas', 'quiz', 'proyek', 'ulangan', 'nilai_akhir', 'created_at', 'updated_at'],
    constraints: {
      unique_composite: [['siswa_id', 'bab']],
      foreignKeys: {
        siswa_id: { table: 'siswas', column: 'id', onDelete: 'cascade' }
      }
    },
    defaults: { tugas: 0, quiz: 0, proyek: 0, ulangan: 0 }
  },
  penilaian_harians: {
    table: 'penilaian_harians',
    primaryKey: 'id',
    columns: ['id', 'kelas_id', 'bab', 'tanggal', 'materi', 'pertemuan', 'created_at', 'updated_at'],
    constraints: {
      unique_composite: [['kelas_id', 'tanggal', 'pertemuan']],
      foreignKeys: {
        kelas_id: { table: 'kelas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  remedials: {
    table: 'remedials',
    primaryKey: 'id',
    columns: ['id', 'nilai_id', 'siswa_id', 'nilai_awal', 'nilai_remedial', 'created_at', 'updated_at'],
    constraints: {
      foreignKeys: {
        nilai_id: { table: 'nilais', column: 'id', onDelete: 'cascade' },
        siswa_id: { table: 'siswas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  jurnal_mengajars: {
    table: 'jurnal_mengajars',
    primaryKey: 'id',
    columns: ['id', 'guru_id', 'kelas_id', 'mata_pelajaran', 'materi', 'jam_ke', 'tanggal', 'created_at', 'updated_at'],
    constraints: {
      foreignKeys: {
        guru_id: { table: 'users', column: 'id', onDelete: 'cascade' },
        kelas_id: { table: 'kelas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  ujians: {
    table: 'ujians',
    primaryKey: 'id',
    columns: ['id', 'judul', 'bab', 'tanggal', 'durasi', 'status', 'token', 'token_expired_at', 'created_at', 'updated_at'],
    constraints: {
      enums: { status: ['draft', 'aktif', 'selesai'] }
    },
    defaults: { durasi: 60, status: 'draft' }
  },
  ujian_kelas: {
    table: 'ujian_kelas',
    primaryKey: 'id',
    columns: ['id', 'ujian_id', 'kelas_id'],
    constraints: {
      unique_composite: [['ujian_id', 'kelas_id']],
      foreignKeys: {
        ujian_id: { table: 'ujians', column: 'id', onDelete: 'cascade' },
        kelas_id: { table: 'kelas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  soals: {
    table: 'soals',
    primaryKey: 'id',
    columns: ['id', 'ujian_id', 'tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'jawaban_benar', 'bobot', 'urutan', 'created_at', 'updated_at'],
    constraints: {
      enums: { tipe: ['pg', 'essay'] },
      foreignKeys: {
        ujian_id: { table: 'ujians', column: 'id', onDelete: 'cascade' }
      }
    },
    defaults: { tipe: 'pg', bobot: 1, urutan: 0 }
  },
  hasil_ujians: {
    table: 'hasil_ujians',
    primaryKey: 'id',
    columns: ['id', 'siswa_id', 'ujian_id', 'nilai_pg', 'nilai_essay', 'nilai_akhir', 'status', 'started_at', 'finished_at', 'tab_switch_count', 'created_at', 'updated_at'],
    constraints: {
      unique_composite: [['siswa_id', 'ujian_id']],
      enums: { status: ['mengerjakan', 'selesai', 'dinilai'] },
      foreignKeys: {
        siswa_id: { table: 'siswas', column: 'id', onDelete: 'cascade' },
        ujian_id: { table: 'ujians', column: 'id', onDelete: 'cascade' }
      }
    },
    defaults: { nilai_pg: 0, nilai_essay: 0, nilai_akhir: 0, status: 'mengerjakan', tab_switch_count: 0 }
  },
  jawaban_siswas: {
    table: 'jawaban_siswas',
    primaryKey: 'id',
    columns: ['id', 'siswa_id', 'ujian_id', 'soal_id', 'jawaban', 'is_correct', 'created_at', 'updated_at'],
    constraints: {
      unique_composite: [['siswa_id', 'soal_id']],
      foreignKeys: {
        siswa_id: { table: 'siswas', column: 'id', onDelete: 'cascade' },
        ujian_id: { table: 'ujians', column: 'id', onDelete: 'cascade' },
        soal_id: { table: 'soals', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  log_ujians: {
    table: 'log_ujians',
    primaryKey: 'id',
    columns: ['id', 'siswa_id', 'ujian_id', 'event', 'detail', 'created_at'],
    constraints: {
      foreignKeys: {
        siswa_id: { table: 'siswas', column: 'id', onDelete: 'cascade' },
        ujian_id: { table: 'ujians', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  materis: {
    table: 'materis',
    primaryKey: 'id',
    columns: ['id', 'kelas_id', 'judul', 'deskripsi', 'file_materi', 'created_at', 'updated_at'],
    constraints: {
      foreignKeys: {
        kelas_id: { table: 'kelas', column: 'id', onDelete: 'cascade' }
      }
    }
  },
  artikels: {
    table: 'artikels',
    primaryKey: 'id',
    columns: ['id', 'judul', 'slug', 'konten', 'gambar', 'created_at', 'updated_at'],
    constraints: {
      unique: ['slug']
    }
  },
  komentars: {
    table: 'komentars',
    primaryKey: 'id',
    columns: ['id', 'artikel_id', 'user_id', 'komentar', 'is_anonim', 'created_at', 'updated_at'],
    constraints: {
      foreignKeys: {
        artikel_id: { table: 'artikels', column: 'id', onDelete: 'cascade' },
        user_id: { table: 'users', column: 'id', onDelete: 'cascade' }
      }
    },
    defaults: { is_anonim: false }
  },
  settings: {
    table: 'settings',
    primaryKey: 'id',
    columns: ['id', 'key', 'value', 'created_at', 'updated_at'],
    constraints: {
      unique: ['key']
    }
  }
};

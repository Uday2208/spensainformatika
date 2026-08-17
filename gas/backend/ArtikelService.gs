/**
 * ArtikelService.gs
 * Backend service untuk pengelolaan Artikel & Berita Web (Fase Artikel)
 * Migrasi dari ArtikelController, GuruController (artikel methods), dan PublicController.
 */

const ArtikelService = {

  /**
   * Helper untuk mengonversi string judul menjadi slug yang SEO friendly
   */
  _slugify: function(text) {
    if (!text) return 'artikel-' + Date.now();
    var slug = text.toString().toLowerCase().trim()
      .replace(/\s+/g, '-')           // Ganti spasi dengan -
      .replace(/[^\w\-]+/g, '')       // Hapus karakter non-alphanumeric selain -
      .replace(/\-\-+/g, '-')         // Ganti multi -- dengan single -
      .replace(/^-+/, '')             // Hapus - di awal
      .replace(/-+$/, '');            // Hapus - di akhir
    return slug || 'artikel-' + Date.now();
  },

  /**
   * Helper untuk memastikan kolom sheet artikels tersedia dan sinkron
   */
  _ensureArtikelsColumns: function() {
    try {
      var sheet = Database.getSpreadsheet().getSheetByName('artikels');
      if (sheet) {
        var lastCol = sheet.getLastColumn();
        var headers = lastCol > 0 ? sheet.getRange(1, 1, 1, lastCol).getValues()[0] : [];
        var requiredCols = ['id', 'judul', 'slug', 'konten', 'gambar', 'created_at', 'updated_at'];
        var appended = false;

        requiredCols.forEach(function(col) {
          if (headers.indexOf(col) === -1) {
            headers.push(col);
            appended = true;
          }
        });

        if (appended) {
          sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
        }
      }
    } catch (e) {
      Logger.log("Error _ensureArtikelsColumns: " + e.message);
    }
  },

  /**
   * Mengambil daftar artikel untuk Panel Guru (terurut terbaru)
   */
  getArtikelInit: function(token) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureArtikelsColumns();

      var artikels = Database.table('artikels').get() || [];

      // Sort DESC berdasarkan created_at atau id
      artikels.sort(function(a, b) {
        var dateA = new Date(a.created_at || 0).getTime();
        var dateB = new Date(b.created_at || 0).getTime();
        if (dateB !== dateA) return dateB - dateA;
        return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
      });

      // Normalisasi format array gambar
      var processed = artikels.map(function(item) {
        var gambarList = [];
        if (item.gambar) {
          if (Array.isArray(item.gambar)) {
            gambarList = item.gambar;
          } else if (typeof item.gambar === 'string') {
            try {
              var parsed = JSON.parse(item.gambar);
              gambarList = Array.isArray(parsed) ? parsed : [item.gambar];
            } catch (e) {
              gambarList = [item.gambar];
            }
          }
        }
        return {
          id: item.id,
          judul: item.judul || '',
          slug: item.slug || '',
          konten: item.konten || '',
          gambar: gambarList,
          created_at: item.created_at || '',
          updated_at: item.updated_at || ''
        };
      });

      return ResponseFormat.success({
        artikels: processed
      });
    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengambil daftar artikel untuk halaman publik dengan pencarian dan paginasi
   */
  getArtikelPublic: function(page, perPage, search) {
    try {
      this._ensureArtikelsColumns();
      var p = parseInt(page) || 1;
      var limit = parseInt(perPage) || 9;
      var q = (search || '').toString().toLowerCase().trim();

      var allArtikels = Database.table('artikels').get() || [];

      // Filter search
      if (q) {
        allArtikels = allArtikels.filter(function(item) {
          var j = (item.judul || '').toLowerCase();
          var k = (item.konten || '').toLowerCase();
          return j.indexOf(q) > -1 || k.indexOf(q) > -1;
        });
      }

      // Sort DESC
      allArtikels.sort(function(a, b) {
        var dateA = new Date(a.created_at || 0).getTime();
        var dateB = new Date(b.created_at || 0).getTime();
        if (dateB !== dateA) return dateB - dateA;
        return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
      });

      var total = allArtikels.length;
      var totalPages = Math.ceil(total / limit) || 1;
      var startIndex = (p - 1) * limit;
      var pagedItems = allArtikels.slice(startIndex, startIndex + limit);

      var processed = pagedItems.map(function(item) {
        var gambarList = [];
        if (item.gambar) {
          if (Array.isArray(item.gambar)) {
            gambarList = item.gambar;
          } else if (typeof item.gambar === 'string') {
            try {
              var parsed = JSON.parse(item.gambar);
              gambarList = Array.isArray(parsed) ? parsed : [item.gambar];
            } catch (e) {
              gambarList = [item.gambar];
            }
          }
        }
        return {
          id: item.id,
          judul: item.judul || '',
          slug: item.slug || '',
          konten: item.konten || '',
          gambar: gambarList,
          created_at: item.created_at || '',
          updated_at: item.updated_at || ''
        };
      });

      return ResponseFormat.success({
        artikels: processed,
        pagination: {
          current_page: p,
          per_page: limit,
          total: total,
          total_pages: totalPages
        }
      });
    } catch (e) {
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Mengambil detail artikel berdasarkan slug atau ID beserta 3 artikel rekomendasi
   */
  getArtikelDetail: function(slugOrId) {
    try {
      this._ensureArtikelsColumns();
      if (!slugOrId) return ResponseFormat.error("Parameter slug atau ID harus diisi.");

      var allArtikels = Database.table('artikels').get() || [];
      var target = null;

      // 1. Cari berdasarkan slug
      target = allArtikels.find(function(a) {
        return a.slug && a.slug.toString() === slugOrId.toString();
      });

      // 2. Fallback cari berdasarkan ID jika numeric
      if (!target && !isNaN(slugOrId)) {
        target = allArtikels.find(function(a) {
          return a.id == slugOrId;
        });
      }

      if (!target) {
        return ResponseFormat.error("Artikel tidak ditemukan.");
      }

      // Normalisasi gambar artikel utama
      var gambarList = [];
      if (target.gambar) {
        if (Array.isArray(target.gambar)) {
          gambarList = target.gambar;
        } else if (typeof target.gambar === 'string') {
          try {
            var parsed = JSON.parse(target.gambar);
            gambarList = Array.isArray(parsed) ? parsed : [target.gambar];
          } catch (e) {
            gambarList = [target.gambar];
          }
        }
      }

      var artikelDetail = {
        id: target.id,
        judul: target.judul || '',
        slug: target.slug || '',
        konten: target.konten || '',
        gambar: gambarList,
        created_at: target.created_at || '',
        updated_at: target.updated_at || ''
      };

      // Ambil 3 rekomendasi artikel lainnya
      var otherArtikels = allArtikels.filter(function(a) {
        return a.id != target.id;
      });

      otherArtikels.sort(function(a, b) {
        var dateA = new Date(a.created_at || 0).getTime();
        var dateB = new Date(b.created_at || 0).getTime();
        if (dateB !== dateA) return dateB - dateA;
        return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
      });

      var rekomendasi = otherArtikels.slice(0, 3).map(function(item) {
        var gList = [];
        if (item.gambar) {
          if (Array.isArray(item.gambar)) {
            gList = item.gambar;
          } else if (typeof item.gambar === 'string') {
            try {
              var parsed = JSON.parse(item.gambar);
              gList = Array.isArray(parsed) ? parsed : [item.gambar];
            } catch (e) {
              gList = [item.gambar];
            }
          }
        }
        return {
          id: item.id,
          judul: item.judul || '',
          slug: item.slug || '',
          konten: item.konten || '',
          gambar: gList,
          created_at: item.created_at || ''
        };
      });

      return ResponseFormat.success({
        artikel: artikelDetail,
        rekomendasi: rekomendasi
      });
    } catch (e) {
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Menyimpan / Publikasi artikel baru
   */
  storeArtikel: function(token, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureArtikelsColumns();

      if (!payload || !payload.judul || !payload.konten) {
        return ResponseFormat.validationError({
          judul: !payload.judul ? ['Judul artikel wajib diisi.'] : undefined,
          konten: !payload.konten ? ['Konten artikel wajib diisi.'] : undefined
        });
      }

      var judul = payload.judul.toString().trim();
      var konten = payload.konten.toString().trim();
      var baseSlug = this._slugify(judul);
      var uniqueSlug = baseSlug;

      // Unique slug collision detection
      var existingArtikels = Database.table('artikels').get() || [];
      var existingSlugs = existingArtikels.map(function(a) { return (a.slug || '').toLowerCase(); });

      var count = 1;
      while (existingSlugs.indexOf(uniqueSlug.toLowerCase()) !== -1) {
        uniqueSlug = baseSlug + '-' + count;
        count++;
      }

      // Normalisasi array gambar
      var gambarPayload = [];
      if (payload.gambar) {
        if (Array.isArray(payload.gambar)) {
          gambarPayload = payload.gambar;
        } else if (typeof payload.gambar === 'string') {
          try {
            var parsed = JSON.parse(payload.gambar);
            gambarPayload = Array.isArray(parsed) ? parsed : [payload.gambar];
          } catch (e) {
            gambarPayload = [payload.gambar];
          }
        }
      }

      var now = new Date().toISOString();
      var dataToInsert = {
        judul: judul,
        slug: uniqueSlug,
        konten: konten,
        gambar: JSON.stringify(gambarPayload),
        created_at: now,
        updated_at: now
      };

      var newId = Database.table('artikels').insert(dataToInsert);

      return ResponseFormat.success({
        id: newId,
        slug: uniqueSlug
      }, "Artikel berhasil dipublikasikan!");

    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Memperbarui artikel yang sudah ada
   */
  updateArtikel: function(token, id, payload) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureArtikelsColumns();

      if (!id) return ResponseFormat.error("ID artikel tidak valid.");
      if (!payload || !payload.judul || !payload.konten) {
        return ResponseFormat.validationError({
          judul: !payload.judul ? ['Judul artikel wajib diisi.'] : undefined,
          konten: !payload.konten ? ['Konten artikel wajib diisi.'] : undefined
        });
      }

      var existing = Database.table('artikels').find(id);
      if (!existing) {
        return ResponseFormat.error("Artikel yang akan diubah tidak ditemukan.");
      }

      var judul = payload.judul.toString().trim();
      var konten = payload.konten.toString().trim();
      var now = new Date().toISOString();

      // Cek apakah judul berubah, jika ya generate slug baru
      var uniqueSlug = existing.slug;
      if (!uniqueSlug || existing.judul !== judul) {
        var baseSlug = this._slugify(judul);
        uniqueSlug = baseSlug;
        var existingArtikels = Database.table('artikels').get() || [];
        var otherSlugs = existingArtikels
          .filter(function(a) { return a.id != id; })
          .map(function(a) { return (a.slug || '').toLowerCase(); });

        var count = 1;
        while (otherSlugs.indexOf(uniqueSlug.toLowerCase()) !== -1) {
          uniqueSlug = baseSlug + '-' + count;
          count++;
        }
      }

      var dataToUpdate = {
        judul: judul,
        slug: uniqueSlug,
        konten: konten,
        updated_at: now
      };

      // Jika ada gambar baru yang dikirimkan, ganti gambar
      if (payload.gambar !== undefined && payload.gambar !== null) {
        var gambarPayload = [];
        if (Array.isArray(payload.gambar)) {
          gambarPayload = payload.gambar;
        } else if (typeof payload.gambar === 'string') {
          try {
            var parsed = JSON.parse(payload.gambar);
            gambarPayload = Array.isArray(parsed) ? parsed : [payload.gambar];
          } catch (e) {
            gambarPayload = [payload.gambar];
          }
        }
        dataToUpdate.gambar = JSON.stringify(gambarPayload);
      }

      Database.table('artikels').where('id', '=', id).update(dataToUpdate);

      return ResponseFormat.success({
        id: id,
        slug: uniqueSlug
      }, "Artikel berhasil diperbarui!");

    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  },

  /**
   * Menghapus artikel
   */
  destroyArtikel: function(token, id) {
    try {
      SessionManager.requireRole(token, 'guru');
      this._ensureArtikelsColumns();

      if (!id) return ResponseFormat.error("ID artikel tidak valid.");

      var existing = Database.table('artikels').find(id);
      if (!existing) {
        return ResponseFormat.error("Artikel tidak ditemukan atau sudah dihapus.");
      }

      Database.table('artikels').where('id', '=', id).delete();

      return ResponseFormat.success(null, "Artikel berhasil dihapus!");

    } catch (e) {
      if (e.message === 'forbidden') return ResponseFormat.forbidden();
      if (e.message === 'unauthorized') return ResponseFormat.unauthorized();
      return ResponseFormat.error(e.message);
    }
  }
};

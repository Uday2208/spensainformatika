/**
 * RouterAuthMiddleware.gs
 * Pengganti Middleware Route Laravel untuk Server-Side Google Apps Script
 */

const RouterAuthMiddleware = {
  
  /**
   * Menentukan halaman mana yang butuh autentikasi dan role apa.
   * Format: 'route': 'required_role' (atau 'auth' untuk semua yang login)
   */
  _routes: {
    '/': 'guest',
    '/login': 'guest',
    '/dashboard-guru': 'guru',
    '/dashboard-siswa': 'siswa',
    '/data-kelas': 'guru',
    '/data-siswa': 'guru',
    '/absensi': 'guru',
    '/penilaian-harian': 'guru',
    '/nilai': 'guru',
    '/materi': 'guru',
    '/artikel': 'guru',
    '/ujian': 'guru',
    '/hasil-ujian': 'guru',
    '/rekap-absensi': 'guru',
    '/rekap-jurnal': 'guru',
    '/kelola-komentar': 'guru',
    '/pengaturan': 'guru',
    '/ujian-siswa': 'siswa',
    '/kehadiran-saya': 'siswa'
  },
  
  /**
   * Memeriksa apakah user berhak mengakses halaman tertentu.
   * Akan me-return true (jika diizinkan), atau path redirect (jika dilarang).
   */
  verifyPageAccess: function(pagePath, token) {
    const requiredRole = this._routes[pagePath];
    const user = SessionManager.getUser(token);
    
    // 1. Halaman tidak terdaftar (404)
    if (!requiredRole) {
      return '/404';
    }
    
    // 2. Halaman Guest (hanya untuk yang belum login)
    if (requiredRole === 'guest') {
      if (user) {
        // Jika sudah login tapi mencoba ke /login, redirect ke dashboard masing-masing
        return user.role === 'guru' ? '/dashboard-guru' : '/dashboard-siswa';
      }
      return true; // Diizinkan
    }
    
    // 3. Halaman yang butuh Login
    if (!user) {
      // Jika belum login, redirect ke login
      return '/login';
    }
    
    // 4. Halaman dengan Role Spesifik (Guru/Siswa)
    if (requiredRole !== 'auth' && user.role !== requiredRole) {
      // Role mismatch (misal Siswa coba akses /dashboard-guru)
      return '/403'; // Forbidden
    }
    
    // Semua cek lolos
    return true;
  }
};

/**
 * AuthService.gs
 * Migrasi dari AuthController.php dengan Security Enhancements (Throttle & Multi-Identifier Login)
 * Mendukung Login Guru (Username) & Siswa (Username / NIS Angka)
 */

const AuthService = {
  login: function(data) {
    const rules = {
      username: 'required',
      password: 'required'
    };
    
    const errors = FormValidator.validate(data, rules);
    if (errors) return ResponseFormat.validationError(errors);
    
    const inputIdentifier = String(data.username || '').trim();
    const inputLower = inputIdentifier.toLowerCase();
    
    // 1. Rate Limiting Check
    const throttle = Security.checkRateLimit(inputLower);
    if (!throttle.allowed) {
      return ResponseFormat.error(throttle.message);
    }
    
    // 2. Cari user berdasarkan username di tabel users
    var allUsers = [];
    try {
      allUsers = Database.table('users').get() || [];
    } catch(e) {
      allUsers = [];
    }

    var user = allUsers.find(function(u) {
      return String(u.username || '').trim().toLowerCase() === inputLower;
    });
    
    var siswaInfo = null;

    // Jika tidak ditemukan di tabel users, periksa apakah input adalah NIS (Angka / String) di tabel siswas
    if (!user) {
      try {
        var allSiswas = Database.table('siswas').get() || [];
        var matchedSiswa = allSiswas.find(function(s) {
          var nisStr = String(s.nis || '').trim();
          var nisnStr = String(s.nisn || '').trim();
          return nisStr === inputIdentifier || nisnStr === inputIdentifier;
        });
        
        if (matchedSiswa && matchedSiswa.user_id) {
          user = allUsers.find(function(u) { return u.id == matchedSiswa.user_id; });
          if (user) {
            siswaInfo = {
              siswa_id: matchedSiswa.id,
              nis: matchedSiswa.nis,
              kelas_id: matchedSiswa.kelas_id
            };
          }
        }
      } catch(e) {
        Logger.log("Error checking siswas table: " + e.message);
      }
    } else if (user.role === 'siswa') {
      try {
        var allSiswas = Database.table('siswas').get() || [];
        var mySiswa = allSiswas.find(function(s) { return s.user_id == user.id; });
        if (mySiswa) {
          siswaInfo = {
            siswa_id: mySiswa.id,
            nis: mySiswa.nis,
            kelas_id: mySiswa.kelas_id
          };
        }
      } catch(e) {}
    }
    
    if (!user) {
      Security.incrementRateLimit(inputLower);
      return ResponseFormat.error('Username / NIS atau password salah.');
    }
    
    // 3. Verifikasi password (Mendukung hash SHA-256 maupun plain-text NIS/password)
    if (!Security.checkPassword(data.password, user.password)) {
      Security.incrementRateLimit(inputLower);
      return ResponseFormat.error('Username / NIS atau password salah.');
    }
    
    // 4. Sukses Login: Bersihkan Throttle
    Security.clearRateLimit(inputLower);
    
    // 5. Create session token
    const token = SessionManager.createSession(user);
    
    // 6. Tentukan Route Redirection berdasarkan Role
    let redirectUrl = '';
    if (user.role === 'guru') {
      redirectUrl = '/dashboard-guru';
    } else if (user.role === 'siswa') {
      redirectUrl = '/dashboard-siswa';
    }
    
    return ResponseFormat.success({
      token: token,
      redirect: redirectUrl,
      user: {
        id: user.id,
        name: String(user.name || 'Pengguna'),
        username: String(user.username || ''),
        role: String(user.role || 'siswa'),
        siswa: siswaInfo
      }
    }, 'Login berhasil');
  },
  
  logout: function(token) {
    if (!token) return ResponseFormat.unauthorized('No session token provided');
    SessionManager.destroySession(token);
    return ResponseFormat.success({ redirect: '/login' }, 'Logout berhasil');
  }
};

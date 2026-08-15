/**
 * AuthService.gs
 * Migrasi dari AuthController.php dengan Security Enhancements (Throttle & Hashing)
 */

const AuthService = {
  login: function(data) {
    const rules = {
      username: 'required',
      password: 'required'
    };
    
    const errors = FormValidator.validate(data, rules);
    if (errors) return ResponseFormat.validationError(errors);
    
    const username = data.username.toLowerCase();
    
    // 1. Rate Limiting Check
    const throttle = Security.checkRateLimit(username);
    if (!throttle.allowed) {
      return ResponseFormat.error(throttle.message);
    }
    
    // 2. Cari user berdasarkan username
    const user = Database.table('users').where('username', '=', username).first();
    
    if (!user) {
      Security.incrementRateLimit(username);
      return ResponseFormat.error('These credentials do not match our records.');
    }
    
    // 3. Verifikasi password dengan Hashing
    if (!Security.checkPassword(data.password, user.password)) {
      Security.incrementRateLimit(username);
      return ResponseFormat.error('These credentials do not match our records.');
    }
    
    // 4. Sukses Login: Bersihkan Throttle
    Security.clearRateLimit(username);
    
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
        name: user.name,
        username: user.username,
        role: user.role
      }
    }, 'Login successful');
  },
  
  logout: function(token) {
    if (!token) return ResponseFormat.unauthorized('No session token provided');
    SessionManager.destroySession(token);
    return ResponseFormat.success({ redirect: '/login' }, 'Logout successful');
  }
};

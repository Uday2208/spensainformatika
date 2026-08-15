/**
 * GasAuthTestRunner.gs
 * Matriks Pengujian untuk Authentication, Hashing, dan Router Middleware
 */

function runAuthTests() {
  Logger.log("--- Starting Auth Matrix Tests ---");

  try {
    const suffix = new Date().getTime();
    
    // 1. Uji Coba Hashing Password
    Logger.log("Test 1: Password Hashing");
    const rawPass = 'Rahasia123!';
    const hashedPass = Security.hashPassword(rawPass);
    if (hashedPass === rawPass) throw new Error("Hash failed, output is still plaintext!");
    if (!Security.checkPassword(rawPass, hashedPass)) throw new Error("Hash verification failed!");
    Logger.log("PASSED: Hashing and Verification works.");

    // 2. Prepare Mock Database dengan Hashed Password
    Logger.log("Preparing mock database...");
    const guruUsername = 'guru_auth_' + suffix;
    const siswaUsername = 'siswa_auth_' + suffix;
    
    Database.table('users').insert({ 
      name: 'Guru Auth', 
      username: guruUsername, 
      password: hashedPass, // Tersimpan sebagai Hash
      role: 'guru' 
    });
    
    Database.table('users').insert({ 
      name: 'Siswa Auth', 
      username: siswaUsername, 
      password: hashedPass, 
      role: 'siswa' 
    });

    // 3. Tes Rate Limiter (Throttle)
    Logger.log("Test 2: Login Rate Limiter (Throttle 5x/min)");
    let throttleHit = false;
    for (let i = 1; i <= 6; i++) {
      const loginAttempt = AuthService.login({ username: guruUsername, password: 'WrongPassword!' });
      if (loginAttempt.message.includes('Too many login attempts')) {
        throttleHit = true;
        if (i <= 5) throw new Error("Throttle hit too early at attempt " + i);
      }
    }
    if (!throttleHit) throw new Error("Throttle failed to block 6th attempt!");
    Logger.log("PASSED: Throttle blocked the 6th consecutive failed login.");

    // Clear throttle agar bisa login benar
    Security.clearRateLimit(guruUsername);
    
    // 4. Tes Login Asli & Dapatkan Token
    Logger.log("Test 3: Authentic Login & Hashing Check");
    const loginGuru = AuthService.login({ username: guruUsername, password: rawPass });
    if (loginGuru.status !== 'success') throw new Error("Guru failed to login with correct password");
    const tokenGuru = loginGuru.data.token;
    
    const loginSiswa = AuthService.login({ username: siswaUsername, password: rawPass });
    const tokenSiswa = loginSiswa.data.token;
    Logger.log("PASSED: Login succeeded, tokens generated.");

    // 5. Tes Matriks Autorisasi Rute (RouterAuthMiddleware)
    Logger.log("Test 4: Authorization Routing Matrix");
    
    // A. Guest (Tanpa Token)
    let res = RouterAuthMiddleware.verifyPageAccess('/dashboard-guru', null);
    if (res !== '/login') throw new Error("Guest -> Guru URL did not redirect to /login");
    
    res = RouterAuthMiddleware.verifyPageAccess('/login', null);
    if (res !== true) throw new Error("Guest -> /login is blocked");

    // B. Guru
    res = RouterAuthMiddleware.verifyPageAccess('/dashboard-guru', tokenGuru);
    if (res !== true) throw new Error("Guru -> Guru URL is blocked");
    
    res = RouterAuthMiddleware.verifyPageAccess('/dashboard-siswa', tokenGuru);
    if (res !== '/403') throw new Error("Guru -> Siswa URL did not return 403 Forbidden");
    
    res = RouterAuthMiddleware.verifyPageAccess('/login', tokenGuru);
    if (res !== '/dashboard-guru') throw new Error("Guru -> /login did not redirect to dashboard");

    // C. Siswa
    res = RouterAuthMiddleware.verifyPageAccess('/dashboard-siswa', tokenSiswa);
    if (res !== true) throw new Error("Siswa -> Siswa URL is blocked");
    
    res = RouterAuthMiddleware.verifyPageAccess('/dashboard-guru', tokenSiswa);
    if (res !== '/403') throw new Error("Siswa -> Guru URL did not return 403 Forbidden");
    
    res = RouterAuthMiddleware.verifyPageAccess('/login', tokenSiswa);
    if (res !== '/dashboard-siswa') throw new Error("Siswa -> /login did not redirect to dashboard");

    Logger.log("PASSED: Route Matrix matches Laravel's behavior.");

    Logger.log("\n✅ ALL AUTHENTICATION TESTS PASSED");

  } catch (error) {
    Logger.log("\n❌ AUTH TEST FAILED: " + error.message);
    Logger.log(error.stack);
  }
}

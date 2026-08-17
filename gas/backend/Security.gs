/**
 * Security.gs
 * Modul untuk Hashing Password dan Login Rate Limiting (Throttle)
 */

const Security = {
  // SECRET SALT (Sebaiknya diganti di production)
  _salt: 'SPENSA_SECRET_SALT_2026!',
  
  /**
   * Menghasilkan hash SHA-256 (Emulasi Hash::make Laravel)
   */
  hashPassword: function(plainText) {
    const rawData = plainText + this._salt;
    const digest = Utilities.computeDigest(Utilities.DigestAlgorithm.SHA_256, rawData);
    
    // Convert byte array to hex string
    let hexString = '';
    for (let i = 0; i < digest.length; i++) {
      let byte = digest[i];
      if (byte < 0) byte += 256;
      let hex = byte.toString(16);
      if (hex.length === 1) hex = '0' + hex;
      hexString += hex;
    }
    return hexString;
  },
  
  /**
   * Memeriksa kecocokan password (Mendukung Plain-Text/NIS maupun Hashing SHA-256)
   */
  checkPassword: function(plainText, hashedPassword) {
    if (hashedPassword === undefined || hashedPassword === null || plainText === undefined || plainText === null) {
      return false;
    }
    const plainStr = String(plainText).trim();
    const storedStr = String(hashedPassword).trim();

    // 1. Direct match (plain-text / NIS numeric)
    if (plainStr === storedStr) return true;

    // 2. SHA-256 with salt match
    const calculatedHash = this.hashPassword(plainStr);
    if (calculatedHash === storedStr) return true;

    return false;
  },
  
  /**
   * Rate Limiter menggunakan CacheService
   * Maksimal 5 attempts per menit per username
   */
  checkRateLimit: function(username) {
    const cache = CacheService.getScriptCache();
    const cacheKey = 'login_attempts_' + username.toLowerCase();
    const attemptsStr = cache.get(cacheKey);
    let attempts = attemptsStr ? parseInt(attemptsStr) : 0;
    
    if (attempts >= 5) {
      return {
        allowed: false,
        message: 'Too many login attempts. Please try again in 1 minute.'
      };
    }
    
    return { allowed: true };
  },
  
  incrementRateLimit: function(username) {
    const cache = CacheService.getScriptCache();
    const cacheKey = 'login_attempts_' + username.toLowerCase();
    const attemptsStr = cache.get(cacheKey);
    let attempts = attemptsStr ? parseInt(attemptsStr) : 0;
    
    attempts++;
    // Simpan di cache selama 60 detik (1 menit timeout)
    cache.put(cacheKey, attempts.toString(), 60); 
  },
  
  clearRateLimit: function(username) {
    const cache = CacheService.getScriptCache();
    const cacheKey = 'login_attempts_' + username.toLowerCase();
    cache.remove(cacheKey);
  }
};

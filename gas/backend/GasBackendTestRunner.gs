/**
 * GasBackendTestRunner.gs
 * Fungsi untuk menguji integritas Backend (Controller/Service Logic).
 */

function runBackendTests() {
  Logger.log("--- Starting Backend Tests ---");

  try {
    // Generate unique suffix to avoid constraint violations on rerun
    const suffix = new Date().getTime();
    
    // PREPARE: Buat data dummy awal menggunakan akses database langsung (tanpa service)
    Logger.log("Preparing mock database...");
    const guruId = Database.table('users').insert({ name: 'Guru Uji', username: 'guru_backend_' + suffix, password: 'pwd', role: 'guru' });
    const kelasId = Database.table('kelas').insert({ nama_kelas: 'Kelas Uji Backend ' + suffix });
    const siswaUserId = Database.table('users').insert({ name: 'Siswa Uji', username: 'siswa_backend_' + suffix, password: 'pwd', role: 'siswa' });
    const siswaId = Database.table('siswas').insert({ user_id: siswaUserId, kelas_id: kelasId, nis: '99' + suffix.toString().slice(-6) });

    // TEST 1: Login & Token Generation
    Logger.log("Test 1: AuthService.login (Guru)");
    const loginGuru = AuthService.login({ username: 'guru_backend_' + suffix, password: 'pwd' });
    if (loginGuru.status !== 'success' || !loginGuru.data.token) throw new Error("Login Guru failed");
    const tokenGuru = loginGuru.data.token;
    Logger.log("PASSED: Guru logged in. Token: " + tokenGuru);

    const loginSiswa = AuthService.login({ username: 'siswa_backend_' + suffix, password: 'pwd' });
    if (loginSiswa.status !== 'success' || !loginSiswa.data.token) throw new Error("Login Siswa failed");
    const tokenSiswa = loginSiswa.data.token;
    Logger.log("PASSED: Siswa logged in. Token: " + tokenSiswa);

    // TEST 2: Role Authorization (Siswa mencoba buat Ujian)
    Logger.log("Test 2: Role Authorization Check");
    const tryCreateUjianSiswa = UjianService.createUjian(tokenSiswa, { judul: 'Ujian Hack', bab: '1', tanggal: '2026-08-14', durasi: 60, kelas_id: kelasId });
    if (tryCreateUjianSiswa.status !== 'forbidden') throw new Error("Siswa should be forbidden from creating Ujian");
    Logger.log("PASSED: Siswa forbidden to act as Guru.");

    // TEST 3: Create Ujian & Soal by Guru
    Logger.log("Test 3: UjianService.createUjian & storeSoal");
    const createUjianRes = UjianService.createUjian(tokenGuru, { judul: 'Ujian Matematika', bab: 'Bab 1', tanggal: '2026-08-14', durasi: 60, kelas_id: kelasId });
    if (createUjianRes.status !== 'success') throw new Error("Failed to create ujian");
    const ujianId = createUjianRes.data.ujian_id;
    
    // Add 2 PG Soals
    UjianService.storeSoal(tokenGuru, ujianId, { tipe: 'pg', pertanyaan: '1+1?', opsi_a: '1', opsi_b: '2', jawaban_benar: 'b', bobot: 50 });
    const soal2Res = UjianService.storeSoal(tokenGuru, ujianId, { tipe: 'pg', pertanyaan: '2*2?', opsi_a: '4', opsi_b: '8', jawaban_benar: 'a', bobot: 50 });
    Logger.log("PASSED: Ujian and Soals created.");
    
    // TEST 4: Activate Ujian
    Logger.log("Test 4: UjianService.activateUjian");
    const activateRes = UjianService.activateUjian(tokenGuru, ujianId);
    if (activateRes.status !== 'success') throw new Error("Failed to activate ujian");
    const tokenUjian = activateRes.data.token;
    Logger.log("PASSED: Ujian activated with token " + tokenUjian);

    // TEST 5: Siswa Masuk Ujian (Wrong Token vs Right Token)
    Logger.log("Test 5: UjianSiswaService.masukUjian");
    const masukWrong = UjianSiswaService.masukUjian(tokenSiswa, ujianId, 'SALAH1');
    if (masukWrong.status !== 'error') throw new Error("Should reject wrong exam token");
    
    const masukRight = UjianSiswaService.masukUjian(tokenSiswa, ujianId, tokenUjian);
    if (masukRight.status !== 'success') throw new Error("Failed to enter exam with right token");
    Logger.log("PASSED: Siswa entered exam.");

    // TEST 6: Save Answers & Submit (Auto Grading)
    Logger.log("Test 6: UjianSiswaService.submitUjian (Auto Grading Check)");
    // Kita tahu soal ID terakhir (soal2Res) dan sebelumnya. Kita asumsikan id soal adalah max dan max-1
    const soals = Database.table('soals').where('ujian_id', '=', ujianId).get();
    
    // Jawab 1 benar (b), 1 salah (b)
    const jawabanPayload = {};
    jawabanPayload[soals[0].id] = 'b'; // Benar (bobot 50)
    jawabanPayload[soals[1].id] = 'b'; // Salah (seharusnya a, bobot 50)
    
    const submitRes = UjianSiswaService.submitUjian(tokenSiswa, ujianId, jawabanPayload);
    if (submitRes.status !== 'success') throw new Error("Failed to submit exam");
    
    // Verifikasi Nilai Akhir
    const hasil = Database.table('hasil_ujians').where('siswa_id', '=', siswaId).where('ujian_id', '=', ujianId).first();
    if (hasil.nilai_pg != 50 || hasil.nilai_akhir != 50) throw new Error("Auto grading calculated incorrectly! Expected 50, got: " + hasil.nilai_pg);
    
    Logger.log("PASSED: Exam submitted and auto-graded correctly (Nilai: " + hasil.nilai_akhir + ").");

    Logger.log("\n✅ ALL BACKEND LOGIC TESTS PASSED");

  } catch (error) {
    Logger.log("\n❌ BACKEND TEST FAILED: " + error.message);
    Logger.log(error.stack);
  }
}

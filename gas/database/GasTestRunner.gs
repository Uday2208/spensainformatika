/**
 * GasTestRunner.gs
 * Fungsi untuk menguji integritas database langsung di dalam Google Apps Script.
 * 
 * CARA PENGGUNAAN:
 * 1. Pastikan Anda sudah menjalankan fungsi initializeDatabase() dari Migration.gs sebelumnya.
 * 2. Pilih fungsi `runDatabaseTests` di dropdown menu editor Apps Script.
 * 3. Klik tombol Run (Jalankan).
 * 4. Buka Execution Log (Log Eksekusi) untuk melihat hasilnya.
 */

function runDatabaseTests() {
  Logger.log("--- Starting Integrity Tests ---");

  try {
    // TEST 1: Insert Valid User
    Logger.log("Test 1: Insert User (Valid)");
    const userId = Database.table('users').insert({ 
      name: 'Admin Uji', 
      username: 'adminuji' + new Date().getTime(), // Pastikan unik setiap kali run
      password: 'pwd' 
    });
    Logger.log("PASSED: User inserted with ID " + userId);

    // TEST 2: Unique Constraint Violation (Users)
    Logger.log("Test 2: Insert Duplicate Username");
    try {
      // Mencoba insert dengan username yang sama
      Database.table('users').insert({ 
        name: 'Admin Uji 2', 
        username: 'admin123', // Pastikan sudah ada 'admin123' di database Anda, atau ganti dengan username yang pasti ada
        password: 'pwd' 
      });
      Logger.log("FAILED: Should have thrown Unique Constraint Error");
    } catch (e) {
      if (e.message.includes('Unique Constraint Violation')) {
        Logger.log("PASSED: " + e.message);
      } else {
        throw e;
      }
    }

    // TEST 3: Insert Valid Kelas
    Logger.log("Test 3: Insert Kelas (Valid)");
    const kelasId = Database.table('kelas').insert({ 
      nama_kelas: 'Kelas Uji ' + new Date().getTime() 
    });
    Logger.log("PASSED: Kelas inserted with ID " + kelasId);
    
    // TEST 4: Insert Valid Siswa (FK Constraint OK)
    Logger.log("Test 4: Insert Siswa (Valid FK)");
    const siswaId = Database.table('siswas').insert({ 
      user_id: userId, 
      kelas_id: kelasId, 
      nis: 'NIS' + new Date().getTime() 
    });
    Logger.log("PASSED: Siswa inserted with ID " + siswaId);

    // TEST 5: FK Constraint Violation (Siswa with invalid user_id)
    Logger.log("Test 5: Insert Siswa (Invalid FK)");
    try {
      Database.table('siswas').insert({ 
        user_id: 999999, // Asumsikan ID ini tidak ada
        kelas_id: kelasId, 
        nis: 'NIS999999' 
      });
      Logger.log("FAILED: Should have thrown FK Error");
    } catch (e) {
      if (e.message.includes('Foreign Key Constraint Violation')) {
        Logger.log("PASSED: " + e.message);
      } else {
        throw e;
      }
    }

    // TEST 6: Composite Unique Constraint (Absensi)
    Logger.log("Test 6: Composite Unique (Absensi)");
    const testDate = '2026-08-14';
    // Insert pertama (berhasil)
    Database.table('absensis').insert({ 
      siswa_id: siswaId, 
      kelas_id: kelasId, 
      tanggal: testDate,
      status: 'hadir'
    });
    
    // Insert kedua (harus gagal)
    try {
      Database.table('absensis').insert({ 
        siswa_id: siswaId, 
        kelas_id: kelasId, 
        tanggal: testDate,
        status: 'sakit'
      });
      Logger.log("FAILED: Should have thrown Composite Unique Error");
    } catch(e) {
      if (e.message.includes('Composite Unique Constraint Violation')) {
        Logger.log("PASSED: " + e.message);
      } else {
        throw e;
      }
    }

    // TEST 7: Enum Validation
    Logger.log("Test 7: Enum Constraint (Ujian Status)");
    try {
      Database.table('ujians').insert({ 
        judul: 'UTS Uji', 
        status: 'status_palsu' // Enum yang diperbolehkan hanya 'draft', 'aktif', 'selesai'
      });
      Logger.log("FAILED: Should have thrown Enum Error");
    } catch(e) {
      if (e.message.includes('not allowed for enum column')) {
        Logger.log("PASSED: " + e.message);
      } else {
        throw e;
      }
    }

    Logger.log("\n✅ ALL INTEGRITY TESTS PASSED");

  } catch (error) {
    Logger.log("\n❌ TEST FAILED: " + error.message);
    Logger.log(error.stack);
  }
}

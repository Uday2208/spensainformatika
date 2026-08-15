/**
 * TestDatabase.js
 * Integrity Test Runner (Local Node.js Simulation)
 */
const fs = require('fs');

// 1. Mock Google Apps Script Environment
global.PropertiesService = {
  getScriptProperties: () => ({ getProperty: () => 'MOCK_SPREADSHEET_ID' })
};
global.LockService = {
  getScriptLock: () => ({ waitLock: () => {}, releaseLock: () => {} })
};

// In-Memory Spreadsheet Mock
class MockSheet {
  constructor(name, headers) {
    this.name = name;
    this.data = [headers];
  }
  getName() { return this.name; }
  getDataRange() {
    return {
      getValues: () => [...this.data]
    };
  }
  getLastColumn() { return this.data[0].length; }
  getRange(r, c, numRows, numCols) {
    const sheet = this;
    return {
      getValues: () => [sheet.data[0]],
      setValues: (values) => {
        if (r === 1) sheet.data[0] = values[0];
        else sheet.data[r - 1] = values[0];
      }
    };
  }
  appendRow(row) {
    this.data.push(row);
  }
}

const mockSpreadsheet = {
  sheets: {},
  getSheets: function() { return Object.values(this.sheets); },
  getSheetByName: function(name) { return this.sheets[name]; },
  insertSheet: function(name) { 
    this.sheets[name] = new MockSheet(name, []);
    return this.sheets[name]; 
  }
};
global.SpreadsheetApp = {
  openById: () => mockSpreadsheet
};
global.Logger = { log: console.log };

// 2. Load the GAS Scripts
function evalFile(filename) {
  const content = fs.readFileSync(__dirname + '/' + filename, 'utf8');
  // Hilangkan const/let global agar bisa menempel ke global context jika pakai eval
  eval(content.replace(/const /g, 'var '));
}

try {
  evalFile('Schema.gs');
  evalFile('Validator.gs');
  evalFile('Database.gs');
  evalFile('Migration.gs');

  console.log("--- Starting Initialization ---");
  // Pre-seed some mock sheets
  for (const [t, s] of Object.entries(DB_SCHEMA)) {
    mockSpreadsheet.sheets[t] = new MockSheet(t, s.columns);
  }
  initializeDatabase();
  console.log("--- DB Initialized ---\n");

  console.log("--- Starting Integrity Tests ---");

  // TEST 1: Insert Valid User
  console.log("Test 1: Insert User (Valid)");
  const userId = Database.table('users').insert({ name: 'Admin', username: 'admin123', password: 'pwd' });
  if (userId !== 1) throw new Error("ID generation failed");
  console.log("PASSED: User ID " + userId);

  // TEST 2: Unique Constraint Violation (Users)
  console.log("Test 2: Insert Duplicate Username");
  try {
    Database.table('users').insert({ name: 'Admin 2', username: 'admin123', password: 'pwd' });
    throw new Error("Should have thrown Unique Constraint Error");
  } catch (e) {
    if (e.message.includes('Unique Constraint Violation')) {
      console.log("PASSED: " + e.message);
    } else throw e;
  }

  // TEST 3: Insert Valid Kelas
  const kelasId = Database.table('kelas').insert({ nama_kelas: '9A' });
  
  // TEST 4: Insert Valid Siswa (FK Constraint OK)
  console.log("Test 4: Insert Siswa (Valid FK)");
  const siswaId = Database.table('siswas').insert({ user_id: userId, kelas_id: kelasId, nis: '12345' });
  console.log("PASSED: Siswa ID " + siswaId);

  // TEST 5: FK Constraint Violation (Siswa with invalid user_id)
  console.log("Test 5: Insert Siswa (Invalid FK)");
  try {
    Database.table('siswas').insert({ user_id: 999, kelas_id: kelasId, nis: '67890' });
    throw new Error("Should have thrown FK Error");
  } catch (e) {
    if (e.message.includes('Foreign Key Constraint Violation')) {
      console.log("PASSED: " + e.message);
    } else throw e;
  }

  // TEST 6: Composite Unique Constraint (Absensi)
  console.log("Test 6: Composite Unique (Absensi)");
  Database.table('absensis').insert({ siswa_id: siswaId, kelas_id: kelasId, tanggal: '2026-08-14' });
  try {
    Database.table('absensis').insert({ siswa_id: siswaId, kelas_id: kelasId, tanggal: '2026-08-14' });
    throw new Error("Should have thrown Composite Unique Error");
  } catch(e) {
    if (e.message.includes('Composite Unique Constraint Violation')) {
      console.log("PASSED: " + e.message);
    } else throw e;
  }

  // TEST 7: Enum Validation
  console.log("Test 7: Enum Constraint (Ujian Status)");
  try {
    Database.table('ujians').insert({ judul: 'UTS', status: 'invalid_status' });
    throw new Error("Should have thrown Enum Error");
  } catch(e) {
    if (e.message.includes('not allowed for enum column')) {
      console.log("PASSED: " + e.message);
    } else throw e;
  }

  console.log("\n✅ ALL INTEGRITY TESTS PASSED");

} catch (e) {
  console.error("❌ TEST FAILED: ", e);
  process.exit(1);
}

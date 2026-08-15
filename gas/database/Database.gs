/**
 * Database.gs
 * Layer Abstraksi Query untuk Google Sheets.
 */

const Database = {
  _currentTable: null,
  _conditions: [],
  _spreadsheetId: PropertiesService.getScriptProperties().getProperty('SPREADSHEET_ID'),
  
  // Ambil instance Spreadsheet
  getSpreadsheet: function() {
    if (!this._spreadsheetId) {
      throw new Error('SPREADSHEET_ID property is not set.');
    }
    return SpreadsheetApp.openById(this._spreadsheetId);
  },
  
  // Set target table (Worksheet)
  table: function(tableName) {
    const clone = Object.assign({}, this);
    clone._currentTable = tableName;
    clone._conditions = [];
    return clone;
  },
  
  // Menambahkan klausa WHERE
  where: function(column, operator, value) {
    if (value === undefined) {
      value = operator;
      operator = '=';
    }
    this._conditions.push({ type: 'where', column, operator, value });
    return this;
  },
  
  whereIn: function(column, values) {
    this._conditions.push({ type: 'whereIn', column, values });
    return this;
  },
  
  _evaluateCondition: function(rowVal, condition) {
    let checkVal = rowVal;
    if (checkVal instanceof Date) {
      // Format to YYYY-MM-DD to match string comparisons
      const offset = checkVal.getTimezoneOffset() * 60000;
      checkVal = (new Date(checkVal.getTime() - offset)).toISOString().split('T')[0];
    }
    
    if (condition.type === 'where') {
      const { operator, value } = condition;
      if (operator === '=') return checkVal == value;
      if (operator === '!=') return checkVal != value;
      if (operator === '>') return checkVal > value;
      if (operator === '<') return checkVal < value;
      if (operator === '>=') return checkVal >= value;
      if (operator === '<=') return checkVal <= value;
    } else if (condition.type === 'whereIn') {
      return condition.values.includes(checkVal);
    }
    return false;
  },
  
  // Ambil semua data sesuai filter
  get: function() {
    const sheet = this.getSpreadsheet().getSheetByName(this._currentTable);
    if (!sheet) throw new Error(`Table ${this._currentTable} not found in Spreadsheet.`);
    
    const dataRange = sheet.getDataRange();
    const values = dataRange.getValues();
    if (values.length <= 1) return []; // Hanya header atau kosong
    
    const headers = values[0];
    const rows = values.slice(1);
    
    let results = [];
    
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const rowObject = {};
      headers.forEach((header, index) => {
        rowObject[header] = row[index];
      });
      
      let match = true;
      for (const cond of this._conditions) {
        if (!this._evaluateCondition(rowObject[cond.column], cond)) {
          match = false;
          break;
        }
      }
      
      if (match) {
        rowObject['_rowIndex'] = i + 2; // Simpan posisi baris di sheet (1-based, +1 header)
        results.push(rowObject);
      }
    }
    
    return results;
  },
  
  first: function() {
    const results = this.get();
    return results.length > 0 ? results[0] : null;
  },
  
  find: function(id) {
    return this.where('id', '=', id).first();
  },
  
  exists: function() {
    return this.first() !== null;
  },
  
  count: function() {
    return this.get().length;
  },
  
  _generateId: function(sheet, headers) {
    const idIndex = headers.indexOf('id');
    if (idIndex === -1) return 1;
    
    const values = sheet.getDataRange().getValues();
    if (values.length <= 1) return 1;
    
    let maxId = 0;
    for (let i = 1; i < values.length; i++) {
      const currentId = parseInt(values[i][idIndex]);
      if (!isNaN(currentId) && currentId > maxId) {
        maxId = currentId;
      }
    }
    return maxId + 1;
  },
  
  // Insert data
  insert: function(dataObject) {
    // Apply defaults from schema
    const schema = DB_SCHEMA[this._currentTable];
    if (schema && schema.defaults) {
      for (const [key, val] of Object.entries(schema.defaults)) {
        if (dataObject[key] === undefined) dataObject[key] = val;
      }
    }
    
    dataObject.created_at = new Date().toISOString();
    dataObject.updated_at = new Date().toISOString();
    
    Validator.validateInsert(this._currentTable, dataObject);
    
    return this.transaction(() => {
      const sheet = this.getSpreadsheet().getSheetByName(this._currentTable);
      const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
      
      if (dataObject.id === undefined) {
        dataObject.id = this._generateId(sheet, headers);
      }
      
      const newRow = [];
      headers.forEach(header => {
        newRow.push(dataObject[header] !== undefined ? dataObject[header] : '');
      });
      
      sheet.appendRow(newRow);
      return dataObject.id;
    });
  },
  
  // Update data
  update: function(dataObject) {
    const target = this.first();
    if (!target) throw new Error("Record not found for update.");
    
    dataObject.updated_at = new Date().toISOString();
    
    Validator.validateUpdate(this._currentTable, target.id, dataObject);
    
    return this.transaction(() => {
      const sheet = this.getSpreadsheet().getSheetByName(this._currentTable);
      const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
      
      const updateArray = [];
      headers.forEach(header => {
        // Jika field diupdate, gunakan nilai baru. Jika tidak, pertahankan nilai lama.
        updateArray.push(dataObject[header] !== undefined ? dataObject[header] : target[header]);
      });
      
      sheet.getRange(target._rowIndex, 1, 1, headers.length).setValues([updateArray]);
      return true;
    });
  },
  
  // Delete data
  delete: function() {
    const targets = this.get();
    if (targets.length === 0) return 0;
    
    return this.transaction(() => {
      const sheet = this.getSpreadsheet().getSheetByName(this._currentTable);
      // Delete dari bawah ke atas agar index baris tidak bergeser salah
      const sortedTargets = targets.sort((a, b) => b._rowIndex - a._rowIndex);
      
      for (const target of sortedTargets) {
        sheet.deleteRow(target._rowIndex);
      }
      return targets.length;
    });
  },
  
  // Transaction Wrapper menggunakan ScriptLock
  transaction: function(callback) {
    const lock = LockService.getScriptLock();
    try {
      lock.waitLock(15000); // Wait up to 15 seconds
      return callback();
    } catch (e) {
      throw new Error("Transaction Failed: " + e.message);
    } finally {
      lock.releaseLock();
    }
  }
};

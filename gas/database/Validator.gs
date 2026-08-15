/**
 * Validator.gs
 * Fungsi untuk memvalidasi constraint (Unique, FK, Enum) sebelum insert/update.
 */

const Validator = {
  validateInsert: function(tableName, data) {
    const schema = DB_SCHEMA[tableName];
    if (!schema) throw new Error(`Schema for table ${tableName} not found.`);
    
    this._checkEnums(schema, data);
    this._checkForeignKeys(schema, data);
    this._checkUniques(schema, data);
  },

  validateUpdate: function(tableName, id, data) {
    const schema = DB_SCHEMA[tableName];
    if (!schema) throw new Error(`Schema for table ${tableName} not found.`);
    
    this._checkEnums(schema, data);
    this._checkForeignKeys(schema, data);
    this._checkUniques(schema, data, id);
  },

  _checkEnums: function(schema, data) {
    if (!schema.constraints || !schema.constraints.enums) return;
    
    for (const [col, allowedValues] of Object.entries(schema.constraints.enums)) {
      if (data[col] !== undefined && !allowedValues.includes(data[col])) {
        throw new Error(`Value '${data[col]}' is not allowed for enum column '${col}' in table '${schema.table}'.`);
      }
    }
  },

  _checkForeignKeys: function(schema, data) {
    if (!schema.constraints || !schema.constraints.foreignKeys) return;
    
    for (const [col, fkDef] of Object.entries(schema.constraints.foreignKeys)) {
      const val = data[col];
      if (val !== undefined && val !== null && val !== '') {
        // Cek apakah data referensi ada di tabel tujuan
        const exists = Database.table(fkDef.table).where(fkDef.column, '=', val).exists();
        if (!exists) {
          throw new Error(`Foreign Key Constraint Violation: '${col}' = '${val}' not found in '${fkDef.table}.${fkDef.column}'.`);
        }
      }
    }
  },

  _checkUniques: function(schema, data, ignoreId = null) {
    if (!schema.constraints) return;
    
    // Check Single Unique Columns
    if (schema.constraints.unique) {
      for (const col of schema.constraints.unique) {
        if (data[col] !== undefined && data[col] !== null && data[col] !== '') {
          let query = Database.table(schema.table).where(col, '=', data[col]);
          if (ignoreId) {
            query = query.where(schema.primaryKey, '!=', ignoreId);
          }
          if (query.exists()) {
            throw new Error(`Unique Constraint Violation: '${col}' = '${data[col]}' already exists in '${schema.table}'.`);
          }
        }
      }
    }
    
    // Check Composite Uniques
    if (schema.constraints.unique_composite) {
      for (const composite of schema.constraints.unique_composite) {
        // Cek apakah semua kolom composite ada di data (jika insert, harus ada. Jika update, cek kombinasi dengan existing)
        // Untuk penyederhanaan saat ini, cek hanya jika seluruh data column tersedia
        let hasAll = true;
        let query = Database.table(schema.table);
        for (const col of composite) {
          if (data[col] === undefined) {
             hasAll = false; break;
          }
          query = query.where(col, '=', data[col]);
        }
        
        if (hasAll) {
          if (ignoreId) {
             query = query.where(schema.primaryKey, '!=', ignoreId);
          }
          if (query.exists()) {
             throw new Error(`Composite Unique Constraint Violation: [${composite.join(', ')}] already exists in '${schema.table}'.`);
          }
        }
      }
    }
  }
};

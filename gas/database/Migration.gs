/**
 * Migration.gs
 * Initializer script untuk setup struktur Google Sheets.
 */

function initializeDatabase() {
  const spreadsheet = Database.getSpreadsheet();
  const existingSheets = spreadsheet.getSheets().map(s => s.getName());
  
  for (const [tableName, schema] of Object.entries(DB_SCHEMA)) {
    let sheet;
    if (!existingSheets.includes(tableName)) {
      sheet = spreadsheet.insertSheet(tableName);
      Logger.log(`Created sheet: ${tableName}`);
    } else {
      sheet = spreadsheet.getSheetByName(tableName);
      Logger.log(`Sheet already exists: ${tableName}`);
    }
    
    // Pastikan header ada di baris pertama
    const headers = schema.columns;
    const currentData = sheet.getDataRange().getValues();
    
    if (currentData.length === 0 || (currentData.length === 1 && currentData[0][0] === '')) {
      sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
      
      // Styling header
      sheet.getRange(1, 1, 1, headers.length).setFontWeight('bold').setBackground('#f3f4f6');
      Logger.log(`Added headers to ${tableName}`);
    }
  }
  
  Logger.log("Database initialization completed successfully.");
}

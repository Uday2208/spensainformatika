/**
 * Relations.gs
 * Fungsi pembantu untuk mengambil data berelasi layaknya ORM.
 */

const Relations = {
  
  // Contoh: JawabanSiswa.belongsTo(Soal) -> Relations.belongsTo(jawaban, 'soals', 'soal_id')
  belongsTo: function(dataObject, parentTable, foreignKeyColumn) {
    if (!dataObject[foreignKeyColumn]) return null;
    return Database.table(parentTable).find(dataObject[foreignKeyColumn]);
  },
  
  // Contoh: Kelas.hasMany(Siswa) -> Relations.hasMany(kelas, 'siswas', 'kelas_id')
  hasMany: function(dataObject, childTable, foreignKeyColumn) {
    if (!dataObject.id) return [];
    return Database.table(childTable).where(foreignKeyColumn, '=', dataObject.id).get();
  },
  
  // Contoh: Ujian.belongsToMany(Kelas) -> Relations.belongsToMany(ujian, 'kelas', 'ujian_kelas', 'ujian_id', 'kelas_id')
  belongsToMany: function(dataObject, relatedTable, pivotTable, foreignKey, relatedKey) {
    if (!dataObject.id) return [];
    
    // Ambil data dari tabel pivot
    const pivotData = Database.table(pivotTable).where(foreignKey, '=', dataObject.id).get();
    if (pivotData.length === 0) return [];
    
    // Extract related IDs
    const relatedIds = pivotData.map(row => row[relatedKey]);
    
    // Ambil data related
    return Database.table(relatedTable).whereIn('id', relatedIds).get();
  }
};

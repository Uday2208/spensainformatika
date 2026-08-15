/**
 * DashboardService.gs
 * Menyediakan agregasi data untuk Modul 1: Dashboard
 */

const DashboardService = {
  
  /**
   * Mengambil statistik untuk Dashboard Guru
   */
  getGuruStats: function(token) {
    const user = SessionManager.getUser(token);
    if (!user || user.role !== 'guru') {
      return ResponseFormat.unauthorized('Akses ditolak');
    }
    
    // Ambil tanggal hari ini format YYYY-MM-DD
    const today = new Date();
    const todayStr = Utilities.formatDate(today, Session.getScriptTimeZone(), "yyyy-MM-dd");
    
    // 1. Total Siswa & Kelas
    const totalSiswa = Database.table('siswas').get().length;
    const totalKelas = Database.table('kelas').get().length;
    
    // 2. Absensi Hari Ini
    const absensis = Database.table('absensis').where('tanggal', '=', todayStr).get();
    
    let hadirHariIni = 0;
    let sakitHariIni = 0;
    let izinHariIni = 0;
    let alphaHariIni = 0;
    
    absensis.forEach(a => {
      if (a.status === 'hadir') hadirHariIni++;
      else if (a.status === 'sakit') sakitHariIni++;
      else if (a.status === 'izin') izinHariIni++;
      else if (a.status === 'alpha') alphaHariIni++;
    });
    
    const sudahDiabsen = absensis.length;
    
    // 3. Total Komentar
    const totalKomentar = Database.table('komentars').get().length;
    
    // 4. Data Grafik 7 Hari Terakhir
    const chartLabels = [];
    const chartData = [];
    
    for (let i = 6; i >= 0; i--) {
      const d = new Date();
      d.setDate(d.getDate() - i);
      const dateStr = Utilities.formatDate(d, Session.getScriptTimeZone(), "yyyy-MM-dd");
      
      const dayData = Database.table('absensis').where('tanggal', '=', dateStr).get();
      let totalHadir = 0;
      dayData.forEach(a => { if (a.status === 'hadir') totalHadir++; });
      
      // Kalkulasi persentase (dibagi total data hari itu, bukan total seluruh siswa)
      let percent = 0;
      if (dayData.length > 0) {
        percent = Math.round((totalHadir / dayData.length) * 100);
      }
      
      // Format tanggal mirip 'D, d M'
      const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
      const dayName = days[d.getDay()];
      const monthName = months[d.getMonth()];
      const dateNum = d.getDate();
      
      chartLabels.push(`${dayName}, ${dateNum} ${monthName}`);
      chartData.push(percent);
    }
    
    return ResponseFormat.success({
      totalSiswa: totalSiswa,
      totalKelas: totalKelas,
      hadirHariIni: hadirHariIni,
      sakitHariIni: sakitHariIni,
      izinHariIni: izinHariIni,
      alphaHariIni: alphaHariIni,
      sudahDiabsen: sudahDiabsen,
      totalKomentar: totalKomentar,
      chartLabels: chartLabels,
      chartData: chartData
    }, 'Data dashboard berhasil diambil');
  }
};

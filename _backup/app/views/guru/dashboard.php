<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<h1 style="margin-bottom: 2rem;">Dashboard Guru</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid var(--primary-blue);">
        <h3>Total Siswa</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $totalSiswa ?></p>
    </div>
    <div class="card" style="border-left: 4px solid var(--success);">
        <h3>Total Kelas</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $totalKelas ?></p>
    </div>
    <div class="card" style="border-left: 4px solid var(--warning);">
        <h3>Hadir Hari Ini</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $hadirHariIni ?></p>
    </div>
    <div class="card" style="border-left: 4px solid var(--danger);">
        <h3>Siswa Remedial</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $siswaRemedial ?></p>
    </div>
</div>

<div class="card">
    <h3>Informasi Sistem</h3>
    <p style="margin-top: 1rem; color: var(--text-muted);">
        Selamat datang di e-Rapor. Gunakan menu di sidebar untuk mengelola data master (kelas dan siswa), melakukan presensi harian, dan memasukkan nilai akademis.
    </p>
</div>

<?php include 'app/views/partials/footer.php'; ?>

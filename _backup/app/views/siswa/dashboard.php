<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<h1 style="margin-bottom: 0.5rem;">Dashboard Siswa</h1>
<p style="color: var(--text-muted); margin-bottom: 2rem;">Selamat datang, <?= htmlspecialchars($siswa['nama']) ?> | <?= htmlspecialchars($siswa['nama_kelas']) ?></p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid var(--success);">
        <h3>Hadir</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $rekapAbsen['hadir'] ?></p>
    </div>
    <div class="card" style="border-left: 4px solid var(--warning);">
        <h3>Sakit / Izin</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $rekapAbsen['sakit'] + $rekapAbsen['izin'] ?></p>
    </div>
    <div class="card" style="border-left: 4px solid var(--danger);">
        <h3>Alpha</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $rekapAbsen['alpha'] ?></p>
    </div>
    <div class="card" style="border-left: 4px solid var(--primary-blue);">
        <h3>Rata-rata Keaktifan</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 1rem;"><?= $rataKeaktifan ?></p>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 1.5rem;">Capaian Rapor Kurikulum Merdeka</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="vertical-align: middle;">Mata Pelajaran</th>
                    <th colspan="3" style="text-align: center; background: #F3F4F6;">Pengetahuan</th>
                    <th colspan="3" style="text-align: center; background: #E5E7EB;">Keterampilan</th>
                    <th rowspan="2" style="vertical-align: middle;">Nilai Akhir Rapor</th>
                </tr>
                <tr>
                    <th style="font-size: 0.85rem; font-weight: normal; background: #F3F4F6;">Formatif (40%)</th>
                    <th style="font-size: 0.85rem; font-weight: normal; background: #F3F4F6;">Sumatif (60%)</th>
                    <th style="font-size: 0.85rem; background: #F3F4F6;">Akhir</th>
                    
                    <th style="font-size: 0.85rem; font-weight: normal; background: #E5E7EB;">Formatif (40%)</th>
                    <th style="font-size: 0.85rem; font-weight: normal; background: #E5E7EB;">Sumatif (60%)</th>
                    <th style="font-size: 0.85rem; background: #E5E7EB;">Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rapor)): ?>
                    <tr><td colspan="8" style="text-align: center;">Belum ada data nilai penilaian</td></tr>
                <?php else: ?>
                    <?php foreach ($rapor as $id => $data): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($data['nama']) ?></strong></td>
                            
                            <!-- Pengetahuan -->
                            <td><?= $data['formatif_pengetahuan'] ?></td>
                            <td><?= $data['sumatif_pengetahuan'] ?></td>
                            <td style="background: #F9FAFB; font-weight: 500; color: var(--primary-blue);">
                                <?= number_format($data['nilai_akhir_pengetahuan'], 2) ?>
                            </td>
                            
                            <!-- Keterampilan -->
                            <td><?= $data['formatif_keterampilan'] ?></td>
                            <td><?= $data['sumatif_keterampilan'] ?></td>
                            <td style="background: #F9FAFB; font-weight: 500; color: var(--success);">
                                <?= number_format($data['nilai_akhir_keterampilan'], 2) ?>
                            </td>
                            
                            <!-- Final -->
                            <td style="font-size: 1.1rem; font-weight: bold; background: #FEF3C7; text-align: center;">
                                <?= number_format($data['nilai_rapor'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3 style="margin-bottom: 1rem;">Ganti Password</h3>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div style="padding: 1rem; background-color: #FEE2E2; color: var(--danger); border-radius: var(--radius-md); margin-bottom: 1rem;">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div style="padding: 1rem; background-color: #DCFCE7; color: var(--success); border-radius: var(--radius-md); margin-bottom: 1rem;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <form action="<?= BASE_URL ?>/app/siswa/password/update" method="POST" style="max-width: 400px;">
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" name="password_baru" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Password</button>
    </form>
</div>

<?php include 'app/views/partials/footer.php'; ?>

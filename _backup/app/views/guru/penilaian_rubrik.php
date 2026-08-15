<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1>Atur Rubrik Penilaian</h1>
        <p style="color: var(--text-muted);">Penilaian: <?= htmlspecialchars($penilaian['nama']) ?></p>
    </div>
    <a href="javascript:history.back()" class="btn" style="background: var(--gray-bg); color: var(--text-main);">Kembali</a>
</div>

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

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <div class="card">
        <h3 style="margin-bottom: 1rem;">Tambah Aspek Rubrik</h3>
        <form action="<?= BASE_URL ?>/app/guru/penilaian/rubrik/store" method="POST">
            <input type="hidden" name="penilaian_master_id" value="<?= $penilaian['id'] ?>">
            
            <div class="form-group">
                <label>Nama Aspek (Contoh: Kerapian, Logika, UI)</label>
                <input type="text" name="nama_aspek" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Skala Maksimal (Misal 4 untuk skala 1-4, atau 100)</label>
                <input type="number" name="skala_maksimal" class="form-control" value="4" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Bobot Aspek (%)</label>
                <input type="number" name="bobot_persen" class="form-control" value="100" min="1" max="100" required>
                <small style="color: var(--text-muted);">Jika menggunakan banyak aspek, pastikan total bobot keseluruhan menjadi 100% (opsional, tergantung cara Anda mengkalkulasi).</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Aspek</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1rem;">Daftar Aspek Rubrik</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama Aspek</th>
                        <th>Skala Maks</th>
                        <th>Bobot (%)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rubrik_list)): ?>
                        <tr><td colspan="4" style="text-align: center;">Belum ada rubrik untuk penilaian ini. <br> Nilai akan diinput secara langsung (0-100).</td></tr>
                    <?php else: ?>
                        <?php foreach ($rubrik_list as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nama_aspek']) ?></strong></td>
                                <td><?= $row['skala_maksimal'] ?></td>
                                <td><?= $row['bobot_persen'] ?>%</td>
                                <td>
                                    <a href="<?= BASE_URL ?>/app/guru/penilaian/rubrik/delete?id=<?= $row['id'] ?>&penilaian_id=<?= $penilaian['id'] ?>" class="btn" style="background: var(--danger); color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Hapus aspek rubrik ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'app/views/partials/footer.php'; ?>

<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manajemen Kelas</h1>
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
        <h3>Tambah Kelas</h3>
        <form action="<?= BASE_URL ?>/app/guru/kelas/store" method="POST" style="margin-top: 1rem;">
            <div class="form-group">
                <label>Nama Kelas (Contoh: X RPL 1)</label>
                <input type="text" name="nama_kelas" class="form-control" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Kelas</h3>
        <div class="table-responsive" style="margin-top: 1rem;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kelas)): ?>
                        <tr><td colspan="3" style="text-align: center;">Belum ada data kelas</td></tr>
                    <?php else: ?>
                        <?php foreach ($kelas as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'app/views/partials/footer.php'; ?>

<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manajemen Siswa</h1>
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
        <h3>Tambah Siswa Baru</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Sistem akan otomatis membuatkan akun login saat siswa ditambahkan.</p>
        <form action="<?= BASE_URL ?>/app/guru/siswa/store" method="POST">
            <div class="form-group">
                <label>NISN</label>
                <input type="text" name="nisn" class="form-control" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Pilih Kelas</label>
                <select name="kelas_id" class="form-control" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Tambah Siswa</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Siswa</h3>
        <div class="table-responsive" style="margin-top: 1rem;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa)): ?>
                        <tr><td colspan="4" style="text-align: center;">Belum ada data siswa</td></tr>
                    <?php else: ?>
                        <?php foreach ($siswa as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row['nisn'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><span style="background: #DBEAFE; color: var(--primary-dark); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-weight: 500;"><?= htmlspecialchars($row['nama_kelas']) ?></span></td>
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

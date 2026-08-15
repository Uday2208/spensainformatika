<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Rekap Kehadiran Siswa</h1>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <form action="" method="GET" style="display: flex; gap: 1rem; align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label>Pilih Kelas</label>
            <select name="kelas_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($selected_kelas): ?>
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th style="text-align: center; color: var(--success);">Hadir</th>
                        <th style="text-align: center; color: var(--warning);">Sakit</th>
                        <th style="text-align: center; color: var(--primary-blue);">Izin</th>
                        <th style="text-align: center; color: var(--danger);">Alpha</th>
                        <th style="text-align: center;">Total Pertemuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekap)): ?>
                        <tr><td colspan="8" style="text-align: center;">Belum ada data siswa di kelas ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rekap as $index => $row): ?>
                            <?php 
                                $total_pertemuan = $row['total_hadir'] + $row['total_sakit'] + $row['total_izin'] + $row['total_alpha']; 
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row['nisn'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td style="text-align: center; font-weight: bold; color: var(--success);"><?= $row['total_hadir'] ?></td>
                                <td style="text-align: center; font-weight: bold; color: var(--warning);"><?= $row['total_sakit'] ?></td>
                                <td style="text-align: center; font-weight: bold; color: var(--primary-blue);"><?= $row['total_izin'] ?></td>
                                <td style="text-align: center; font-weight: bold; color: var(--danger);"><?= $row['total_alpha'] ?></td>
                                <td style="text-align: center; font-weight: 500;"><?= $total_pertemuan ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="text-align: center; color: var(--text-muted); padding: 3rem;">
        Silakan pilih kelas terlebih dahulu untuk melihat rekap kehadiran.
    </div>
<?php endif; ?>

<?php include 'app/views/partials/footer.php'; ?>

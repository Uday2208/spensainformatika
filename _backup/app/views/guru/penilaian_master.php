<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manajemen Penilaian (Kurikulum Merdeka)</h1>
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

<div class="card" style="margin-bottom: 2rem;">
    <form action="" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
            <label>Tahun Ajaran</label>
            <select name="tahun_ajaran_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Pilih TA --</option>
                <?php foreach ($ta as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($selected_ta == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['nama'] . ' - ' . $t['semester']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
            <label>Mata Pelajaran</label>
            <select name="mata_pelajaran_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Pilih Mapel --</option>
                <?php foreach ($mapel as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= ($selected_mapel == $m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
            <label>Kelas</label>
            <select name="kelas_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($selected_kelas && $selected_mapel && $selected_ta): ?>
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">Buat Penilaian Baru</h3>
        <form action="<?= BASE_URL ?>/app/guru/penilaian/store" method="POST" style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <input type="hidden" name="kelas_id" value="<?= $selected_kelas ?>">
            <input type="hidden" name="mata_pelajaran_id" value="<?= $selected_mapel ?>">
            <input type="hidden" name="tahun_ajaran_id" value="<?= $selected_ta ?>">
            
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label>Nama Penilaian</label>
                <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ulangan Harian 1 / Proyek Akhir">
            </div>
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label>Jenis Evaluasi</label>
                <select name="jenis_evaluasi" class="form-control" required>
                    <option value="Formatif">Formatif</option>
                    <option value="Sumatif">Sumatif</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label>Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="Pengetahuan">Pengetahuan</option>
                    <option value="Keterampilan">Keterampilan</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label>Tipe Tugas</label>
                <select name="tipe" class="form-control" required>
                    <option value="Tugas">Tugas</option>
                    <option value="Kuis">Kuis</option>
                    <option value="LKPD">LKPD</option>
                    <option value="Diskusi">Diskusi</option>
                    <option value="Ulangan">Ulangan</option>
                    <option value="Proyek">Proyek</option>
                    <option value="Praktik">Praktik</option>
                </select>
            </div>
            <div style="width: 100%;">
                <button type="submit" class="btn btn-primary">Tambah Penilaian</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1rem;">Daftar Penilaian</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Penilaian</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($penilaian_list)): ?>
                        <tr><td colspan="5" style="text-align: center;">Belum ada penilaian untuk kelas ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($penilaian_list as $row): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td>
                                    <span style="background: <?= $row['jenis_evaluasi'] == 'Sumatif' ? '#FEE2E2' : '#E0E7FF' ?>; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                        <?= $row['jenis_evaluasi'] ?>
                                    </span>
                                    <span style="background: #F3F4F6; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin-left: 0.5rem;">
                                        <?= $row['kategori'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['tipe']) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="<?= BASE_URL ?>/app/guru/penilaian/rubrik?id=<?= $row['id'] ?>" class="btn" style="background: var(--warning); color: var(--text-main); padding: 0.4rem 0.8rem; font-size: 0.85rem;">Rubrik</a>
                                        <a href="<?= BASE_URL ?>/app/guru/penilaian/input?id=<?= $row['id'] ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Input Nilai</a>
                                        <a href="<?= BASE_URL ?>/app/guru/penilaian/delete?id=<?= $row['id'] ?>&kelas_id=<?= $selected_kelas ?>&mapel_id=<?= $selected_mapel ?>&ta_id=<?= $selected_ta ?>" class="btn" style="background: var(--danger); color: white; padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Hapus penilaian ini berserta seluruh nilainya?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="text-align: center; color: var(--text-muted); padding: 3rem;">
        Silakan lengkapi pilihan Tahun Ajaran, Mata Pelajaran, dan Kelas terlebih dahulu.
    </div>
<?php endif; ?>

<?php include 'app/views/partials/footer.php'; ?>

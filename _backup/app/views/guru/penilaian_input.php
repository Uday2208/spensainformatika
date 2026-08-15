<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1>Input Nilai</h1>
        <p style="color: var(--text-muted);">
            <?= htmlspecialchars($penilaian['nama_ta']) ?> | 
            <?= htmlspecialchars($penilaian['nama_mapel']) ?> | 
            Kelas: <?= htmlspecialchars($penilaian['nama_kelas']) ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/app/guru/penilaian?kelas_id=<?= $penilaian['kelas_id'] ?>&mata_pelajaran_id=<?= $penilaian['mata_pelajaran_id'] ?>&tahun_ajaran_id=<?= $penilaian['tahun_ajaran_id'] ?>" class="btn" style="background: var(--gray-bg); color: var(--text-main);">Kembali</a>
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
    <h3>Informasi Penilaian</h3>
    <ul style="margin-top: 1rem; color: var(--text-muted); list-style: none; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
        <li><strong>Nama:</strong> <?= htmlspecialchars($penilaian['nama']) ?></li>
        <li><strong>Tanggal:</strong> <?= date('d M Y', strtotime($penilaian['tanggal'])) ?></li>
        <li><strong>Tipe:</strong> <?= htmlspecialchars($penilaian['tipe']) ?></li>
        <li><strong>Kategori:</strong> <?= htmlspecialchars($penilaian['kategori']) ?> (<?= htmlspecialchars($penilaian['jenis_evaluasi']) ?>)</li>
    </ul>
</div>

<form action="<?= BASE_URL ?>/app/guru/penilaian/input/store" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="penilaian_id" value="<?= $penilaian['id'] ?>">
    
    <div class="card">
        <h3 style="margin-bottom: 1rem;">Daftar Siswa</h3>
        <?php if ($is_rubrik): ?>
            <div style="background: var(--gray-bg); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: 0.9rem;">
                <strong>Info Rubrik:</strong> Nilai akhir akan dihitung otomatis berdasarkan bobot tiap aspek.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <?php if ($is_rubrik): ?>
                            <?php foreach ($rubrik_list as $r): ?>
                                <th style="min-width: 120px;">
                                    <?= htmlspecialchars($r['nama_aspek']) ?><br>
                                    <small style="color: var(--text-muted); font-weight: normal;">(Max: <?= $r['skala_maksimal'] ?>, Bobot: <?= $r['bobot_persen'] ?>%)</small>
                                </th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th style="width: 150px;">Nilai Akhir (0-100)</th>
                        <?php endif; ?>
                        
                        <?php if ($is_rubrik): ?>
                            <th style="width: 100px;">Nilai Akhir</th>
                        <?php endif; ?>
                        <th style="min-width: 150px;">Upload File</th>
                        <th style="min-width: 150px;">Catatan Guru</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa)): ?>
                        <tr><td colspan="<?= $is_rubrik ? count($rubrik_list) + 5 : 5 ?>" style="text-align: center;">Belum ada siswa di kelas ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($siswa as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($row['nisn'] ?? '-') ?></small>
                                </td>
                                
                                <?php if ($is_rubrik): ?>
                                    <?php foreach ($rubrik_list as $r): ?>
                                        <?php $skor_val = isset($rubrik_detail[$row['id']][$r['id']]) ? $rubrik_detail[$row['id']][$r['id']] : ''; ?>
                                        <td>
                                            <input type="number" name="rubrik_skor[<?= $row['id'] ?>][<?= $r['id'] ?>]" class="form-control" min="0" max="<?= $r['skala_maksimal'] ?>" step="0.01" value="<?= $skor_val ?>" style="width: 100%;">
                                        </td>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <td>
                                        <input type="number" name="nilai[<?= $row['id'] ?>]" class="form-control" placeholder="0-100" min="0" max="100" step="0.01" value="<?= htmlspecialchars($row['nilai_akhir'] ?? '') ?>" style="width: 100%;">
                                    </td>
                                <?php endif; ?>
                                
                                <?php if ($is_rubrik): ?>
                                    <td style="font-weight: bold; text-align: center;">
                                        <?= htmlspecialchars(number_format((float)($row['nilai_akhir'] ?? 0), 2)) ?>
                                    </td>
                                <?php endif; ?>
                                
                                <td>
                                    <?php if (!empty($row['file_upload'])): ?>
                                        <div style="margin-bottom: 0.5rem; font-size: 0.85rem;">
                                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($row['file_upload']) ?>" target="_blank" style="color: var(--primary-blue); text-decoration: underline;">Lihat File</a>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="file_upload[<?= $row['id'] ?>]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png,.zip" style="font-size: 0.8rem; padding: 0.3rem;">
                                </td>
                                
                                <td>
                                    <input type="text" name="catatan[<?= $row['id'] ?>]" class="form-control" placeholder="Tingkatkan belajarmu..." value="<?= htmlspecialchars($row['catatan_guru'] ?? '') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 3rem;">Simpan Nilai</button>
        </div>
    </div>
</form>

<?php include 'app/views/partials/footer.php'; ?>

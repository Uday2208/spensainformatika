<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<style>
    @media (max-width: 768px) {
        .responsive-table thead { display: none; }
        .responsive-table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-md);
            padding: 1rem;
            background: #fff;
        }
        .responsive-table tbody td {
            display: block;
            padding: 0.5rem 0;
            text-align: left;
        }
        .responsive-table tbody td:first-child { display: none; } /* Hide 'No' on mobile */
        .responsive-table tbody td:nth-child(2) { font-weight: bold; font-size: 1.1rem; }
        .responsive-table .status-options {
            display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.5rem;
        }
        .responsive-table .status-options label {
            display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;
        }
        .responsive-table .desktop-labels { display: none; }
    }
    
    @media (min-width: 769px) {
        .responsive-table .mobile-labels { display: none; }
    }
    
    .sticky-bottom {
        position: sticky; bottom: 0; background: var(--white); padding: 1rem; box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.1); border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; z-index: 10;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Input Absensi</h1>
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
    <form action="" method="GET" style="display: flex; gap: 1rem; align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label>Pilih Kelas</label>
            <select name="kelas_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Pilih --</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($selected_kelas && !empty($siswa)): ?>
    <form action="<?= BASE_URL ?>/app/guru/absensi/store" method="POST" autocomplete="off" id="form-absensi">
        <input type="hidden" name="kelas_id" value="<?= $selected_kelas ?>">
        
        <div class="card" style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label>Pertemuan Ke-</label>
                <input type="number" name="pertemuan" class="form-control" min="1" required>
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end; margin-bottom: 0;">
                <button type="button" class="btn" style="background: var(--gray-bg); color: var(--text-main);" onclick="setSemuaHadir()">Set Semua Hadir</button>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th style="width: 300px;">Status Kehadiran</th>
                            <th style="width: 150px;">Nilai Keaktifan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td>
                                    <div class="status-options" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                        <label><input type="radio" name="absensi[<?= $row['id'] ?>]" value="hadir" class="radio-hadir" checked> <span class="desktop-labels">H</span><span class="mobile-labels">Hadir</span></label>
                                        <label><input type="radio" name="absensi[<?= $row['id'] ?>]" value="sakit"> <span class="desktop-labels">S</span><span class="mobile-labels">Sakit</span></label>
                                        <label><input type="radio" name="absensi[<?= $row['id'] ?>]" value="izin"> <span class="desktop-labels">I</span><span class="mobile-labels">Izin</span></label>
                                        <label><input type="radio" name="absensi[<?= $row['id'] ?>]" value="alpha"> <span class="desktop-labels">A</span><span class="mobile-labels">Alpha</span></label>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="nilai_keaktifan[<?= $row['id'] ?>]" class="form-control" placeholder="0-100" min="0" max="100" style="padding: 0.4rem; margin-top: 0.5rem; width: 100%;">
                                </td>
                                <td>
                                    <input type="text" name="keterangan[<?= $row['id'] ?>]" class="form-control" placeholder="Keterangan (Opsional)" style="padding: 0.4rem; margin-top: 0.5rem;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sticky-bottom">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 3rem;">SIMPAN ABSENSI</button>
        </div>
    </form>
    
        <script>
        function setSemuaHadir() {
            var radios = document.querySelectorAll('.radio-hadir');
            for (var i = 0; i < radios.length; i++) {
                radios[i].checked = true;
            }
        }
        
        // Pastikan tercentang secara otomatis saat halaman dimuat
        window.addEventListener('load', function() {
            // Jalankan segera
            setSemuaHadir();
            // Jalankan juga setelah sedikit jeda (untuk browser yang me-restore state agak lambat)
            setTimeout(setSemuaHadir, 100);
            setTimeout(setSemuaHadir, 500);
        });
        </script>
<?php elseif ($selected_kelas): ?>
    <div class="card" style="text-align: center; color: var(--text-muted);">
        Kelas ini belum memiliki data siswa. Silakan tambahkan siswa terlebih dahulu di menu Manajemen Siswa.
    </div>
<?php endif; ?>

<?php include 'app/views/partials/footer.php'; ?>

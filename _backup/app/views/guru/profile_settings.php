<?php include 'app/views/partials/header.php'; ?>
<?php include 'app/views/partials/sidebar.php'; ?>

<h1 style="margin-bottom: 2rem;">Pengaturan Web Profile</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" style="padding: 1rem; background-color: var(--success); color: white; margin-bottom: 1rem; border-radius: 4px;">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" style="padding: 1rem; background-color: var(--danger); color: white; margin-bottom: 1rem; border-radius: 4px;">
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card">
    <form action="<?= BASE_URL ?>/app/guru/profile-settings/store" method="POST">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Judul Utama (Hero Title)</label>
            <input type="text" name="hero_title" value="<?= htmlspecialchars($profile['hero_title'] ?? '') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px;" required>
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Sub-judul Utama (Hero Subtitle)</label>
            <textarea name="hero_subtitle" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px;" rows="3" required><?= htmlspecialchars($profile['hero_subtitle'] ?? '') ?></textarea>
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Teks Tentang (About)</label>
            <textarea name="about_text" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px;" rows="5"><?= htmlspecialchars($profile['about_text'] ?? '') ?></textarea>
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Kontak</label>
            <input type="email" name="contact_email" value="<?= htmlspecialchars($profile['contact_email'] ?? '') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nomor Telepon Kontak</label>
            <input type="text" name="contact_phone" value="<?= htmlspecialchars($profile['contact_phone'] ?? '') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Alamat</label>
            <textarea name="address" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px;" rows="3"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn" style="background-color: var(--primary-blue); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
            Simpan Pengaturan
        </button>
    </form>
</div>

<?php include 'app/views/partials/footer.php'; ?>

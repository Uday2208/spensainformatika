<?php $role = $_SESSION['user_role'] ?? ''; ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        e-Rapor <?= ucfirst($role) ?>
    </div>
    <nav class="sidebar-nav">
        <?php if ($role == 'guru'): ?>
            <a href="<?= BASE_URL ?>/app/guru/dashboard">Dashboard</a>
            <a href="<?= BASE_URL ?>/app/guru/kelas">Manajemen Kelas</a>
            <a href="<?= BASE_URL ?>/app/guru/siswa">Manajemen Siswa</a>
            <a href="<?= BASE_URL ?>/app/guru/absensi">Absensi Siswa</a>
            <a href="<?= BASE_URL ?>/app/guru/rekap-absensi">Rekap Kehadiran</a>
            <a href="<?= BASE_URL ?>/app/guru/penilaian">Penilaian</a>
            <a href="<?= BASE_URL ?>/app/guru/profile-settings">Web Profile</a>
        <?php elseif ($role == 'siswa'): ?>
            <a href="<?= BASE_URL ?>/app/siswa/dashboard">Dashboard Siswa</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/logout" style="color: #EF4444; margin-top: auto;">Logout</a>
    </nav>
</aside>

<main class="main-content">
    <header class="top-header">
        <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
            ☰
        </button>
        <div class="user-info">
            Halo, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
        </div>
    </header>
    <div class="content-area">

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($profile['hero_subtitle'] ?? 'Web Profile Guru Informatika') ?>">
    <title><?= htmlspecialchars($profile['hero_title'] ?? 'Profil Guru Informatika') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        :root {
            --brand-primary: #3B82F6; /* Blue for informatics theme */
            --brand-secondary: #06B6D4;
            --brand-accent: #8B5CF6;
            --bg-dark: #0F172A;
            --bg-light: #F8FAFC;
            --text-main: #1E293B;
            --text-muted: #64748B;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-main);
            background-color: var(--bg-light);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
            margin-top: 0;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-nav a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            transition: color 0.3s;
        }

        .navbar-nav a:hover {
            color: var(--brand-primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: white !important;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-dark);
            position: relative;
            overflow: hidden;
            padding: 6rem 5% 2rem;
            box-sizing: border-box;
            text-align: left;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, rgba(15, 23, 42, 0) 70%);
            top: -200px;
            left: -200px;
            border-radius: 50%;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, rgba(15, 23, 42, 0) 70%);
            bottom: -100px;
            right: -100px;
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 1000px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4rem;
        }

        .hero-text {
            flex: 1;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            animation: fadeIn 1.5s ease-out;
        }

        .hero-image img {
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 4px solid rgba(255,255,255,0.1);
        }

        .hero h1 {
            font-size: 3.5rem;
            color: white;
            line-height: 1.2;
            margin-bottom: 1rem;
            letter-spacing: -1px;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 span {
            background: linear-gradient(135deg, #60A5FA, #34D399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero h2 {
            font-size: 1.5rem;
            color: #34D399;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: fadeInUp 1s ease-out 0.1s both;
        }

        .hero p {
            font-size: 1.15rem;
            color: #94a3b8;
            margin-bottom: 2.5rem;
            line-height: 1.6;
            max-width: 500px;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-btns {
            animation: fadeInUp 1s ease-out 0.4s both;
            display: flex;
            gap: 1rem;
        }

        .hero .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-size: 1.125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
        }

        .hero .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .hero .btn-secondary {
            display: inline-block;
            background: transparent;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-size: 1.125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .hero .btn-secondary:hover {
            border-color: white;
            background: rgba(255,255,255,0.1);
        }

        /* About Section */
        .section {
            padding: 6rem 5%;
            box-sizing: border-box;
        }

        .about-section {
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--text-main);
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--brand-primary);
            border-radius: 2px;
        }

        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            font-size: 1.125rem;
            line-height: 1.8;
            color: var(--text-muted);
        }

        /* Portfolio / Skills Section */
        .portfolio-section {
            background: var(--bg-light);
        }

        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .skill-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border-top: 4px solid var(--brand-primary);
        }

        .skill-card:hover {
            transform: translateY(-5px);
        }

        .skill-card h3 {
            font-size: 1.25rem;
            color: var(--text-main);
            margin-bottom: 1rem;
        }

        /* Contact Section */
        .contact-section {
            background: white;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .contact-card {
            background: var(--bg-light);
            padding: 2.5rem;
            border-radius: 16px;
            text-align: center;
            transition: transform 0.3s;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            background: var(--brand-primary);
            color: white;
        }
        
        .contact-card:hover h3, 
        .contact-card:hover p {
            color: white;
        }

        .contact-card h3 {
            font-size: 1.25rem;
            color: var(--text-main);
            margin-bottom: 1rem;
            transition: color 0.3s;
        }

        .contact-card p {
            color: var(--text-muted);
            line-height: 1.6;
            transition: color 0.3s;
        }

        /* Footer */
        footer {
            background: var(--bg-dark);
            color: white;
            text-align: center;
            padding: 2rem 5%;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                text-align: center;
            }
            .hero-image { display: none; }
            .hero h1 { font-size: 2.5rem; }
            .hero p { margin: 0 auto 2.5rem; }
            .hero-btns { justify-content: center; }
            .navbar-nav { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="#" class="navbar-brand">Profil Guru</a>
        <div class="navbar-nav">
            <a href="#home">Home</a>
            <a href="#about">Tentang Saya</a>
            <a href="#portfolio">Portofolio</a>
            <a href="#contact">Kontak</a>
            <a href="<?= BASE_URL ?>/login" class="btn-login">Login Siswa</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h2>Halo, Selamat Datang di Web Profil Saya</h2>
                <h1>
                    <?php 
                    $title = htmlspecialchars($profile['hero_title'] ?? 'Guru Informatika'); 
                    $words = explode(' ', $title);
                    $last_word = array_pop($words);
                    echo implode(' ', $words) . ' <span>' . $last_word . '</span>';
                    ?>
                </h1>
                <p><?= htmlspecialchars($profile['hero_subtitle'] ?? 'Membangun generasi melek teknologi melalui pendidikan informatika yang interaktif dan menyenangkan.') ?></p>
                <div class="hero-btns">
                    <a href="#about" class="btn-primary">Mengenal Lebih Jauh</a>
                    <a href="#portfolio" class="btn-secondary">Lihat Portofolio</a>
                </div>
            </div>
            <!-- Dummy Image placeholder representing the teacher -->
            <div class="hero-image">
                <img src="https://ui-avatars.com/api/?name=Guru+Informatika&size=400&background=0D8ABC&color=fff&font-size=0.33" alt="Profil Guru">
            </div>
        </div>
    </section>

    <!-- About Section -->
    <?php if(!empty($profile['about_text'])): ?>
    <section id="about" class="section about-section">
        <div class="section-header">
            <h2>Tentang Saya</h2>
        </div>
        <div class="about-content">
            <p><?= nl2br(htmlspecialchars($profile['about_text'])) ?></p>
        </div>
    </section>
    <?php endif; ?>

    <!-- Portfolio Section -->
    <section id="portfolio" class="section portfolio-section">
        <div class="section-header">
            <h2>Portofolio & Keahlian</h2>
        </div>
        <div class="skills-grid">
            <div class="skill-card">
                <h3>Pemrograman Web</h3>
                <p>Menguasai HTML, CSS, JavaScript, dan PHP untuk membangun aplikasi web modern.</p>
            </div>
            <div class="skill-card">
                <h3>Jaringan Komputer</h3>
                <p>Instalasi, konfigurasi, dan manajemen jaringan komputer lokal (LAN).</p>
            </div>
            <div class="skill-card">
                <h3>Desain Grafis</h3>
                <p>Pembuatan aset visual pembelajaran menggunakan Photoshop dan Illustrator.</p>
            </div>
            <div class="skill-card">
                <h3>Sistem E-Rapor</h3>
                <p>Pengembangan sistem akademik dan penilaian siswa berbasis digital.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section contact-section">
        <div class="section-header">
            <h2>Hubungi Saya</h2>
        </div>
        <div class="contact-grid">
            <div class="contact-card">
                <h3>Email</h3>
                <p><?= htmlspecialchars($profile['contact_email'] ?? 'guru@sekolah.com') ?></p>
            </div>
            <div class="contact-card">
                <h3>Telepon / WhatsApp</h3>
                <p><?= htmlspecialchars($profile['contact_phone'] ?? '+62 812-3456-7890') ?></p>
            </div>
            <div class="contact-card">
                <h3>Alamat Sekolah</h3>
                <p><?= nl2br(htmlspecialchars($profile['address'] ?? 'Jl. Pendidikan No. 1, Jakarta')) ?></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> Profil Guru Informatika. Sistem E-Rapor.</p>
    </footer>

</body>
</html>

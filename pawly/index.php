<?php
require_once 'koneksi.php';
require_once 'layout_bg.php';

$sudah_login  = isset($_SESSION['user_id']);
$tujuan_mulai = $sudah_login ? 'user/dashboard.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pawly - Pantau Sehat Kucingmu</title>
    <?= pawly_bg_style(); ?>
    <?= pawly_font_style(); ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { color: #3D2C2E; line-height: 1.5; }
        a { text-decoration: none; color: inherit; }

        .navbar {
            background: rgba(255, 255, 255, 0.95); padding: 14px 6%; display: flex; justify-content: space-between;
            align-items: center; border-bottom: 1px solid #F3DCD6; position: sticky; top: 0; z-index: 30;
            backdrop-filter: blur(4px);
        }
        .brand { display: inline-flex; align-items: center; }
        .nav-links { display: flex; gap: 24px; font-size: 14px; font-weight: 600; }
        .nav-links a:hover { color: #D96B78; }
        .nav-actions { display: flex; gap: 10px; align-items: center; }
        .btn-pink {
            background: #E58C96; color: #FFF; padding: 10px 20px; border-radius: 999px;
            font-size: 14px; font-weight: 700; display: inline-block;
        }
        .btn-outline {
            border: 1.5px solid #E58C96; color: #C95B68; padding: 8px 16px; border-radius: 999px;
            font-size: 14px; font-weight: 700; display: inline-block; background: #FFF;
        }

        .hero-slider {
            position: relative; max-width: 1140px; margin: 24px auto 0; border-radius: 26px;
            background: linear-gradient(125deg, #FFE8EC 0%, #FFF5DC 100%);
            border: 1px solid #F3DCD6;
            box-shadow: 0 12px 28px rgba(180, 110, 120, 0.12);
            padding: 28px 64px;
            overflow: hidden;
        }
        .hero-slide { display: none; width: 100%; }
        .hero-slide.active { display: block; }
        .hero-inner {
            display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 28px;
            align-items: center; min-height: 330px;
        }
        @media (max-width: 820px) {
            .hero-slider { padding: 24px 20px 54px; }
            .hero-inner { grid-template-columns: 1fr; text-align: center; }
            .hero-text p { margin: 0 auto 18px; }
        }
        .hero-text h1 { font-size: 34px; line-height: 1.22; margin-bottom: 12px; color: #3A2528; }
        .hero-text p { font-size: 15px; max-width: 470px; margin-bottom: 20px; color: #634B4E; }

        .hero-photo-frame {
            width: 300px; height: 300px; max-width: 100%; margin: 0 auto;
            border-radius: 24px; overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 20px rgba(120, 75, 82, 0.12);
            background: #FFFFFF;
            display: flex; align-items: center; justify-content: center;
            padding: 12px;
        }
        .hero-photo-frame img {
            max-width: 100%; max-height: 100%;
            width: auto; height: auto;
            object-fit: contain;
            display: block;
            filter: none;
        }

        .slider-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 42px; height: 42px; border-radius: 50%; border: 1px solid #EED4CE;
            background: #FFFFFF; color: #3D2C2E; font-size: 22px;
            font-weight: bold; cursor: pointer; z-index: 20;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            line-height: 1;
        }
        .slider-btn:hover { background: #FFF5F6; }
        .slider-btn.prev { left: 14px; }
        .slider-btn.next { right: 14px; }

        .slider-dots {
            position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
            display: flex; gap: 8px; z-index: 20;
        }
        .dot {
            width: 10px; height: 10px; border-radius: 50%; border: none;
            background: rgba(90, 46, 51, 0.25); cursor: pointer;
        }
        .dot.active { background: #D96B78; width: 24px; border-radius: 999px; }

        .section { max-width: 1140px; margin: 56px auto; padding: 0 20px; text-align: center; }
        .section h2 { font-size: 26px; margin-bottom: 8px; }
        .section .sub { color: #755B5E; font-size: 14px; margin-bottom: 28px; }

        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; text-align: left; }
        .card {
            background: rgba(255, 255, 255, 0.96); border: 1px solid #F3E2DD; border-radius: 20px; padding: 22px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
        }
        .badge-step {
            display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center;
            border-radius: 50%; background: #FFE3E6; color: #C95B68; font-weight: 800; margin-bottom: 12px;
        }
        .card h3 { font-size: 18px; margin-bottom: 8px; }
        .card p { font-size: 14px; color: #6A5356; }

        .cta-box {
            max-width: 1100px; margin: 48px auto; padding: 40px 24px; border-radius: 24px;
            text-align: center; background: linear-gradient(120deg, #F7A8B0 0%, #F7DF9C 100%);
            color: #3D2C2E;
        }
        .cta-box h2 { font-size: 28px; margin-bottom: 10px; }
        .cta-box p { max-width: 520px; margin: 0 auto 20px; font-size: 14px; }
        .btn-dark {
            background: #5A2E33; color: #FFF; padding: 11px 24px; border-radius: 999px;
            font-weight: 700; font-size: 14px; display: inline-block;
        }

        footer {
            background: #FFF2EF; border-top: 1px solid #F1DCD6; padding: 24px 6%;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 13px; color: #755B5E;
        }
    </style>
</head>
<body class="pawly-bg">

<header class="navbar">
    <a href="index.php" class="brand"><?= pawly_logo_html(); ?></a>
    <nav class="nav-links">
        <a href="#fitur">Fitur</a>
        <a href="#panduan">Panduan</a>
        <a href="<?= $tujuan_mulai; ?>">Dashboard</a>
    </nav>
    <div class="nav-actions">
        <?php if ($sudah_login): ?>
            <a href="<?= $tujuan_mulai; ?>" class="btn-pink">Buka Dashboard</a>
            <a href="logout.php" class="btn-outline">Keluar</a>
        <?php else: ?>
            <a href="login.php" class="btn-outline">Masuk</a>
            <a href="register.php" class="btn-pink">Daftar Akun</a>
        <?php endif; ?>
    </div>
</header>

<section class="hero-slider" id="heroSlider">
    <div class="hero-slide active">
        <div class="hero-inner">
            <div class="hero-text">
                <h1>Pantau Sehat Kucingmu dengan Cara Seru & Gemas</h1>
                <p>Simpan profil kucing, berat badan, nafsu makan, status vaksin, dan catatan kesehatan harian dalam satu tempat yang rapi.</p>
                <div><a href="<?= $tujuan_mulai; ?>" class="btn-pink">Buka Dashboard</a></div>
            </div>
            <div class="hero-photo-frame">
                <img src="assets/hero1.jpg" alt="Kucing lucu sehat">
            </div>
        </div>
    </div>

    <div class="hero-slide">
        <div class="hero-inner">
            <div class="hero-text">
                <h1>Nggak Lupa Lagi Jadwal & Urutan Vaksin</h1>
                <p>Catat status serta jenis vaksin yang sudah diberikan dan lihat panduan urutan vaksinasi sesuai usia kucingmu.</p>
                <div><a href="<?= $tujuan_mulai; ?>" class="btn-pink">Buka Dashboard</a></div>
            </div>
            <div class="hero-photo-frame">
                <img src="assets/hero2.jpg" alt="Catatan vaksin kucing">
            </div>
        </div>
    </div>

    <div class="hero-slide">
        <div class="hero-inner">
            <div class="hero-text">
                <h1>Buku Harian Anabul untuk Pemilik Kucing</h1>
                <p>Pantau grafik berat badan, nafsu makan, dan saran perawatan harian agar kucing tetap aktif dan sehat.</p>
                <div><a href="<?= $sudah_login ? $tujuan_mulai : 'register.php'; ?>" class="btn-pink"><?= $sudah_login ? 'Buka Dashboard' : 'Buat Akun Gratis'; ?></a></div>
            </div>
            <div class="hero-photo-frame">
                <img src="assets/hero3.jpg" alt="Perawatan harian anabul">
            </div>
        </div>
    </div>

    <button type="button" class="slider-btn prev" id="btnPrev" aria-label="Sebelumnya">&lsaquo;</button>
    <button type="button" class="slider-btn next" id="btnNext" aria-label="Berikutnya">&rsaquo;</button>

    <div class="slider-dots">
        <button type="button" class="dot active" data-slide="0" aria-label="Slide 1"></button>
        <button type="button" class="dot" data-slide="1" aria-label="Slide 2"></button>
        <button type="button" class="dot" data-slide="2" aria-label="Slide 3"></button>
    </div>
</section>

<section id="fitur" class="section">
    <h2>Semua Kebutuhan Sehat si Meow</h2>
    <p class="sub">Dirancang ringan dan ramah untuk mencatat kondisi kucing tanpa ribet.</p>
    <div class="grid-3">
        <div class="card">
            <h3>Profil & Berat Kucing</h3>
            <p>Pilih ras (termasuk Campuran), catat umur dalam bulan, berat badan, dan nafsu makan lalu perbarui kapan saja.</p>
        </div>
        <div class="card">
            <h3>Jenis & Urutan Vaksin</h3>
            <p>Catat jenis vaksin yang sudah diberikan serta lihat panduan urutan vaksin sesuai usia dan kondisi kucing.</p>
        </div>
        <div class="card">
            <h3>Saran Perawatan Harian</h3>
            <p>Dapatkan saran praktis saat nafsu makan menurun, berat badan perlu dipantau, atau jadwal vaksin mendekat.</p>
        </div>
    </div>
</section>

<section id="panduan" class="section">
    <h2>Cara Mudah Mulai Merawat di Pawly</h2>
    <p class="sub">Cuma butuh beberapa langkah singkat untuk mulai mencatat data.</p>
    <div class="grid-3">
        <div class="card">
            <div class="badge-step">1</div>
            <h3>Tambah Kucing</h3>
            <p>Masuk ke akun lalu tambahkan profil kucing beserta ras, umur, dan berat badannya.</p>
        </div>
        <div class="card">
            <div class="badge-step">2</div>
            <h3>Catat Kondisi & Vaksin</h3>
            <p>Isi nafsu makan, status serta jenis vaksin, dan catatan kesehatan si kucing.</p>
        </div>
        <div class="card">
            <div class="badge-step">3</div>
            <h3>Update Rutin</h3>
            <p>Perbarui data saat berat badan atau kondisi berubah agar otomatis tercatat ke grafik dan riwayat.</p>
        </div>
    </div>
</section>

<section id="tentang" class="cta-box">
    <h2>Yuk, Jadikan Kucingmu Anabul Paling Bahagia & Sehat!</h2>
    <p>Mulai kelola profil, jenis vaksin, dan catatan kesehatan kucing dengan mudah di Pawly.</p>
    <a href="<?= $sudah_login ? $tujuan_mulai : 'register.php'; ?>" class="btn-dark"><?= $sudah_login ? 'Buka Dashboard Sekarang' : 'Daftar Akun Gratis Sekarang'; ?></a>
</section>

<footer>
    <span><strong>Pawly</strong> &mdash; Catat & Pantau Kesehatan Kucing</span>
    <span>Beranda &bull; Fitur &bull; Panduan &bull; Dashboard</span>
</footer>

<script>
(function () {
    var currentSlide = 0;
    var slides = document.querySelectorAll('#heroSlider .hero-slide');
    var dots = document.querySelectorAll('#heroSlider .dot');
    var prevBtn = document.getElementById('btnPrev');
    var nextBtn = document.getElementById('btnNext');
    var autoTimer = null;
    var intervalMs = 4000;

    function showSlide(index) {
        if (!slides.length) return;
        currentSlide = (index + slides.length) % slides.length;
        for (var i = 0; i < slides.length; i++) {
            slides[i].classList.remove('active');
            slides[i].style.display = 'none';
        }
        slides[currentSlide].classList.add('active');
        slides[currentSlide].style.display = 'block';

        for (var j = 0; j < dots.length; j++) {
            dots[j].classList.remove('active');
        }
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }

    function startAutoSlide() {
        stopAutoSlide();
        autoTimer = setInterval(function () {
            showSlide(currentSlide + 1);
        }, intervalMs);
    }

    function stopAutoSlide() {
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }
    }

    if (prevBtn) {
        prevBtn.onclick = function () {
            showSlide(currentSlide - 1);
            startAutoSlide();
        };
    }

    if (nextBtn) {
        nextBtn.onclick = function () {
            showSlide(currentSlide + 1);
            startAutoSlide();
        };
    }

    for (var k = 0; k < dots.length; k++) {
        (function (idx) {
            dots[idx].onclick = function () {
                showSlide(idx);
                startAutoSlide();
            };
        })(k);
    }

    showSlide(0);
    startAutoSlide();
})();
</script>
</body>
</html>
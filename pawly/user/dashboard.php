<?php
require_once '../koneksi.php';
require_once '../layout_bg.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id  = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

function saranBelumVaksin($usia_bulan) {
    $usia = (int)$usia_bulan;
    if ($usia < 2) {
        return "Usia masih di bawah 2 bulan, sebaiknya fokus nutrisi dan kehangatan dulu; vaksin inti umumnya dimulai sekitar usia 6–8 minggu setelah cek dokter hewan.";
    }
    if ($usia <= 4) {
        return "Karena belum vaksin (usia {$usia} bulan), sebaiknya mulai dari vaksin inti dasar seperti F3 / Tricat dulu, baru lanjut ke dosis lanjutan/F4 sesuai jadwal dokter hewan.";
    }
    return "Karena belum vaksin (usia {$usia} bulan), sebaiknya mulai dengan vaksin inti dasar (F3 / Tricat) terlebih dahulu, lalu lanjutkan ke F4 dan Rabies setelah diperiksa dokter hewan.";
}

function saranSetelahJenisVaksin($jenis_vaksin) {
    $jenis = trim((string)$jenis_vaksin);

    if ($jenis === '' || $jenis === '-') {
        return "Sudah tercatat vaksin, namun jenis vaksinnya belum dipilih. Silakan update jenis vaksin agar saran lanjutan lebih spesifik.";
    }
    if (stripos($jenis, 'F3') !== false || stripos($jenis, 'Tricat') !== false) {
        return "Sudah mendapat vaksin F3 / Tricat — langkah berikutnya biasanya vaksin lanjutan / F4 (sesuai jadwal interval) dan vaksin Rabies saat usia mencukupi.";
    }
    if (stripos($jenis, 'F4') !== false || stripos($jenis, 'Tetracat') !== false) {
        return "Sudah mendapat vaksin F4 / Tetracat — setelah ini umumnya dilanjutkan ke vaksin Rabies atau booster sesuai jadwal dokter hewan.";
    }
    if (stripos($jenis, 'Rabies') !== false) {
        return "Sudah mendapat vaksin Rabies — pastikan vaksin inti (seperti F3/F4) juga sudah lengkap dan ikuti jadwal booster rutin tiap tahun.";
    }
    if (stripos($jenis, 'Lengkap') !== false || stripos($jenis, 'Booster') !== false) {
        return "Vaksin sudah lengkap / booster — cukup jaga kondisi harian dan catat jadwal booster tahunan berikutnya.";
    }

    return "Sudah tercatat vaksin {$jenis} — ikuti jadwal dosis lanjutan atau booster berikutnya sesuai anjuran dokter hewan.";
}

function evaluasiKesehatan($usia_bulan, $berat_kg, $nafsu_makan, $status_vaksin, $jenis_vaksin = '-') {
    $usia   = (int)$usia_bulan;
    $berat  = (float)$berat_kg;
    $nafsu  = trim((string)$nafsu_makan);
    $vaksin = trim((string)$status_vaksin);
    $jenis  = trim((string)$jenis_vaksin);

    $batas_min_berat = ($usia < 6) ? 0.5 : 2.8;
    $batas_max_berat = ($usia < 6) ? 3.5 : 7.0;
    $berat_ideal     = ($berat >= $batas_min_berat && $berat <= $batas_max_berat);

    $nafsu_baik = ($nafsu === 'Baik / Aktif');
    $vaksin_ok  = ($vaksin === 'Sudah Vaksin');

    // 1. Nafsu makan menurun atau lesu
    if (!$nafsu_baik) {
        if ($nafsu === 'Lesu') {
            $saran = "Kucing terlihat lesu dan nafsu makannya turun. Pastikan air minum tersedia, cek kondisinya, dan tunda vaksinasi sampai pulih atau konsultasi ke dokter hewan.";
        } else {
            $saran = "Nafsu makan sedang menurun. Coba beri pakan basah yang lebih beraroma dan pertimbangkan vitamin/suplemen nafsu makan sesuai anjuran dokter hewan sebelum divaksin.";
        }
        return [
            'label' => ($nafsu === 'Lesu') ? 'Perlu Perhatian (Lesu)' : 'Perlu Perhatian (Nafsu Turun)',
            'kelas' => 'perhatian',
            'saran' => $saran
        ];
    }

    // 2. Nafsu makan baik, tapi berat badan di luar rentang ideal
    if (!$berat_ideal) {
        $saran = ($berat < $batas_min_berat)
            ? "Nafsu makan sudah baik, namun berat badannya masih kurang untuk usianya. Fokus perbaiki porsi/nutrisi dulu dan pantau kenaikan beratnya."
            : "Nafsu makan baik, namun berat badannya cukup tinggi untuk usianya. Atur takaran porsi/snack dan ajak bermain rutin.";

        return [
            'label' => 'Perlu Pantau Berat Badan',
            'kelas' => 'pantau',
            'saran' => $saran
        ];
    }

    // 3. Nafsu makan baik & berat ideal, tapi belum vaksin -> sarankan vaksin awal
    if (!$vaksin_ok) {
        return [
            'label' => 'Cukup Sehat (Perlu Vaksin)',
            'kelas' => 'pantau',
            'saran' => saranBelumVaksin($usia)
        ];
    }

    // 4. Nafsu makan baik, berat ideal, dan sudah vaksin -> sarankan tahap setelah jenis vaksin tersebut
    return [
        'label' => 'Kualitas Sangat Baik',
        'kelas' => 'sehat',
        'saran' => saranSetelahJenisVaksin($jenis)
    ];
}

$cari   = trim($_GET['cari'] ?? '');
$filter = $_GET['vaksin'] ?? '';

$where = "WHERE user_id = '$user_id'";
if ($cari !== '') {
    $cari_aman = mysqli_real_escape_string($koneksi, $cari);
    $where .= " AND (nama_kucing LIKE '%$cari_aman%' OR ras LIKE '%$cari_aman%')";
}
if ($filter === 'Sudah Vaksin' || $filter === 'Belum Vaksin') {
    $where .= " AND status_vaksin = '$filter'";
}

$result = mysqli_query($koneksi, "SELECT * FROM kucing $where ORDER BY id DESC");

$semua_q = mysqli_query($koneksi, "SELECT * FROM kucing WHERE user_id = '$user_id' ORDER BY id DESC");
$daftar_kucing = [];
$total_vaksin = 0;
$total_sehat  = 0;

if ($semua_q) {
    while ($k = mysqli_fetch_assoc($semua_q)) {
        $daftar_kucing[] = $k;
        if (($k['status_vaksin'] ?? '') === 'Sudah Vaksin') {
            $total_vaksin++;
        }
        $ev = evaluasiKesehatan(
            $k['usia_bulan'] ?? 0,
            $k['berat_kg'] ?? 0,
            $k['nafsu_makan'] ?? 'Baik / Aktif',
            $k['status_vaksin'] ?? 'Belum Vaksin',
            $k['jenis_vaksin'] ?? '-'
        );
        if ($ev['kelas'] === 'sehat') {
            $total_sehat++;
        }
    }
}
$total_kucing = count($daftar_kucing);

$kucing_aktif_id = isset($_GET['kucing_id']) ? (int)$_GET['kucing_id'] : ($daftar_kucing[0]['id'] ?? 0);
$kucing_aktif = null;
foreach ($daftar_kucing as $dk) {
    if ((int)$dk['id'] === $kucing_aktif_id) {
        $kucing_aktif = $dk;
        break;
    }
}
if (!$kucing_aktif && !empty($daftar_kucing)) {
    $kucing_aktif = $daftar_kucing[0];
    $kucing_aktif_id = (int)$kucing_aktif['id'];
}

$riwayat_list = [];
if ($kucing_aktif_id > 0) {
    $rq = mysqli_query($koneksi, "SELECT * FROM riwayat_pemantauan WHERE kucing_id = '$kucing_aktif_id' ORDER BY tanggal_catat ASC, id ASC");
    if ($rq) {
        while ($r = mysqli_fetch_assoc($rq)) {
            $riwayat_list[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pawly - Pantau Sehat Kucingmu</title>
    <?= pawly_bg_style(); ?>
    <?= pawly_font_style(); ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; display: flex; flex-direction: column; color: #3D2C2E; }

        .navbar {
            background: rgba(255, 255, 255, 0.95); padding: 14px 6%; display: flex;
            justify-content: space-between; align-items: center; border-bottom: 1px solid #F3DCD6;
            position: sticky; top: 0; z-index: 10; backdrop-filter: blur(4px);
        }
        .brand { display: inline-flex; align-items: center; text-decoration: none; }
        .nav-menu { display: flex; gap: 20px; align-items: center; font-size: 14px; font-weight: 600; }
        .nav-menu a { text-decoration: none; color: #5C4649; }
        .nav-menu a.aktif { color: #D96B78; }
        .user-pill { background: #FFEFF1; color: #A63F4C; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .btn-logout { background: #F3E2DD; color: #3D2C2E; padding: 7px 15px; border-radius: 999px; text-decoration: none; font-size: 13px; font-weight: 700; }

        .container { flex: 1; max-width: 1140px; width: 100%; margin: 28px auto; padding: 0 20px; }

        .hero-mini {
            background: linear-gradient(120deg, #FFE6E9 0%, #FFF4D8 100%);
            border: 1px solid #F5D5D9; border-radius: 20px; padding: 22px 26px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;
            margin-bottom: 22px;
        }
        .hero-mini h1 { font-size: 22px; margin-bottom: 4px; }
        .hero-mini p { font-size: 14px; color: #6A5356; }
        .btn-primary {
            background: #E58C96; color: #FFF; padding: 10px 20px; border-radius: 999px;
            text-decoration: none; font-weight: 700; font-size: 14px; display: inline-block;
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px; }
        .stat-card { background: rgba(255,255,255,0.96); border: 1px solid #F3E2DD; border-radius: 18px; padding: 18px 20px; }
        .stat-card span { font-size: 13px; color: #755B5E; font-weight: 600; }
        .stat-card h3 { font-size: 26px; margin-top: 6px; color: #D96B78; }

        .tracker-panel {
            background: rgba(255,255,255,0.97); border: 1px solid #F3E2DD; border-radius: 22px; padding: 22px;
            margin-bottom: 26px; box-shadow: 0 6px 20px rgba(0,0,0,0.03);
        }
        .tracker-head {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
        }
        .tracker-head h2 { font-size: 19px; }
        .tracker-head select {
            padding: 8px 14px; border: 1px solid #E2C8C2; border-radius: 999px;
            font-size: 13px; font-weight: 600; background: #FFFDFC;
        }
        .tracker-title-wrap { display: flex; align-items: center; gap: 12px; }
        .tracker-avatar {
            width: 54px; height: 54px; border-radius: 16px; object-fit: cover;
            border: 2px solid #F3DCD6; background: #FFF; flex-shrink: 0;
        }
        .tracker-grid {
            display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; align-items: start;
        }
        @media (max-width: 820px) {
            .tracker-grid { grid-template-columns: 1fr; }
        }
        .chart-box {
            background: #FFF9F7; border: 1px solid #F5E5E0; border-radius: 16px; padding: 16px;
        }
        .saran-box {
            margin-top: 12px; background: #FFFDFC; border-left: 4px solid #E58C96;
            padding: 11px 14px; border-radius: 8px; font-size: 13px; color: #5C4649; line-height: 1.5;
        }
        .saran-box.perhatian { border-left-color: #E11D48; background: #FFF5F7; }
        .saran-box.pantau { border-left-color: #D97706; background: #FFFBEB; }
        .saran-box.sehat { border-left-color: #16A34A; background: #F0FDF4; }

        .history-table-wrap { max-height: 270px; overflow-y: auto; border: 1px solid #F5E7E3; border-radius: 14px; }
        .history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .history-table th, .history-table td { padding: 9px 11px; border-bottom: 1px solid #F5E7E3; text-align: left; }
        .history-table th { background: #FFEFF1; position: sticky; top: 0; }

        .filter-bar {
            background: rgba(255,255,255,0.96); border: 1px solid #F3E2DD; border-radius: 16px; padding: 14px 18px;
            display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 18px;
        }
        .filter-bar input, .filter-bar select {
            padding: 9px 13px; border: 1px solid #E2C8C2; border-radius: 999px; font-size: 13px;
        }
        .filter-bar input { flex: 1; min-width: 200px; }
        .btn-filter {
            background: #5A2E33; color: #FFF; border: none; padding: 9px 18px;
            border-radius: 999px; font-size: 13px; font-weight: 700; cursor: pointer;
        }
        .btn-reset { font-size: 13px; color: #755B5E; text-decoration: none; }

        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(315px, 1fr)); gap: 18px; }
        .cat-card {
            background: rgba(255,255,255,0.97); border: 1px solid #F3E2DD; border-radius: 20px; padding: 20px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;
        }
        .cat-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 12px; }
        .cat-profile-wrap { display: flex; align-items: center; gap: 11px; }
        .cat-avatar {
            width: 48px; height: 48px; border-radius: 14px; object-fit: cover;
            border: 2px solid #F3DCD6; background: #FFF; flex-shrink: 0;
        }
        .cat-name { font-size: 18px; font-weight: 800; }
        .cat-breed { font-size: 13px; color: #755B5E; }

        .badge { padding: 5px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .badge.sehat { background: #DCFCE7; color: #166534; }
        .badge.pantau { background: #FEF3C7; color: #92400E; }
        .badge.perhatian { background: #FFE4E6; color: #9F1239; }

        .cat-metrics {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
            background: #FFF9F7; padding: 10px; border-radius: 12px; margin-bottom: 12px; text-align: center;
        }
        .cat-metrics div small { display: block; font-size: 11px; color: #755B5E; }
        .cat-metrics div strong { font-size: 13px; }

        .cat-note { font-size: 13px; color: #5C4649; margin-bottom: 10px; line-height: 1.5; }
        .card-tip {
            font-size: 12px; color: #5C4649; background: #FFF9F7; border-radius: 10px;
            padding: 8px 10px; margin-bottom: 14px; border: 1px solid #F4E2DC; line-height: 1.45;
        }
        .card-tip.perhatian { background: #FFF1F3; border-color: #FECDD3; color: #881337; }
        .card-tip.pantau { background: #FFFBEB; border-color: #FDE68A; color: #78350F; }

        .cat-footer {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;
            border-top: 1px solid #F5E7E3; padding-top: 12px; font-size: 13px;
        }
        .aksi a { margin-left: 8px; text-decoration: none; font-weight: 700; }
        .aksi .grafik { color: #9D174D; }
        .aksi .edit { color: #2563EB; }
        .aksi .hapus { color: #DC2626; }

        footer {
            background: #FFF2EF; border-top: 1px solid #F1DCD6; padding: 20px 6%;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;
            font-size: 13px; color: #755B5E; margin-top: 40px;
        }
    </style>
</head>
<body class="pawly-bg">

<header class="navbar">
    <a href="../index.php" class="brand"><?= pawly_logo_html(); ?></a>
    <nav class="nav-menu">
        <a href="../index.php">Beranda</a>
        <a href="dashboard.php" class="aktif">Dashboard</a>
        <a href="tambah.php">+ Catat Kucing</a>
    </nav>
    <div style="display:flex; gap:10px; align-items:center;">
        <span class="user-pill">Halo, <?= htmlspecialchars($username); ?></span>
        <a href="../logout.php" class="btn-logout">Keluar</a>
    </div>
</header>

<main class="container">
    <section class="hero-mini">
        <div>
            <h1>Pemantauan Kesehatan & Perkembangan Kucing</h1>
            <p>Pantau foto & kondisi harian, jenis vaksin, saran lanjutan vaksin, dan grafik berat badan anabulmu.</p>
        </div>
        <a href="tambah.php" class="btn-primary">+ Tambah Kucing Baru</a>
    </section>

    <section class="stats-grid">
        <div class="stat-card"><span>Total Kucing Terdaftar</span><h3><?= $total_kucing; ?> Ekor</h3></div>
        <div class="stat-card"><span>Status Sudah Vaksin</span><h3><?= $total_vaksin; ?> / <?= $total_kucing; ?></h3></div>
        <div class="stat-card"><span>Kualitas Sangat Baik</span><h3><?= $total_sehat; ?> Ekor</h3></div>
    </section>

    <?php if ($kucing_aktif):
        $eval_aktif = evaluasiKesehatan(
            $kucing_aktif['usia_bulan'] ?? 0,
            $kucing_aktif['berat_kg'] ?? 0,
            $kucing_aktif['nafsu_makan'] ?? 'Baik / Aktif',
            $kucing_aktif['status_vaksin'] ?? 'Belum Vaksin',
            $kucing_aktif['jenis_vaksin'] ?? '-'
        );
    ?>
    <section class="tracker-panel">
        <div class="tracker-head">
            <div class="tracker-title-wrap">
                <img src="<?= pawly_foto_kucing_src($kucing_aktif['foto_kucing'] ?? '', '../'); ?>"
                     alt="<?= htmlspecialchars($kucing_aktif['nama_kucing']); ?>"
                     class="tracker-avatar">
                <div>
                    <h2>Grafik Berat & Riwayat Kesehatan: <?= htmlspecialchars($kucing_aktif['nama_kucing']); ?></h2>
                    <span class="badge <?= $eval_aktif['kelas']; ?>" style="margin-top:6px; display:inline-block;">
                        <?= htmlspecialchars($eval_aktif['label']); ?>
                    </span>
                </div>
            </div>

            <form method="GET" style="display:flex; gap:8px; align-items:center;">
                <label for="pilih_kucing" style="font-size:13px; font-weight:600;">Pilih Kucing:</label>
                <select id="pilih_kucing" name="kucing_id" onchange="this.form.submit()">
                    <?php foreach ($daftar_kucing as $dk): ?>
                        <option value="<?= (int)$dk['id']; ?>" <?= (int)$dk['id'] === $kucing_aktif_id ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($dk['nama_kucing']); ?> (<?= htmlspecialchars($dk['ras']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="tracker-grid">
            <div class="chart-box">
                <div style="font-size:13px; font-weight:700; margin-bottom:8px;">Diagram Perkembangan Berat Badan (Kg)</div>
                <?php if (count($riwayat_list) > 0):
                    $max_berat = 0.5;
                    foreach ($riwayat_list as $rw) {
                        if ((float)$rw['berat_kg'] > $max_berat) {
                            $max_berat = (float)$rw['berat_kg'];
                        }
                    }
                    $max_berat = ceil($max_berat + 0.5);
                    $svg_w = 500;
                    $svg_h = 170;
                    $pad_l = 36;
                    $pad_r = 20;
                    $pad_t = 18;
                    $pad_b = 28;
                    $plot_w = $svg_w - $pad_l - $pad_r;
                    $plot_h = $svg_h - $pad_t - $pad_b;
                    $count_r = count($riwayat_list);
                    $points = [];
                    foreach ($riwayat_list as $idx => $rw) {
                        $x = ($count_r === 1) ? ($pad_l + $plot_w / 2) : ($pad_l + ($idx / ($count_r - 1)) * $plot_w);
                        $y = $pad_t + $plot_h - (((float)$rw['berat_kg'] / $max_berat) * $plot_h);
                        $points[] = [
                            'x'     => round($x, 1),
                            'y'     => round($y, 1),
                            'berat' => $rw['berat_kg'],
                            'tgl'   => substr((string)$rw['tanggal_catat'], 5)
                        ];
                    }
                    $polyline_parts = [];
                    foreach ($points as $p) {
                        $polyline_parts[] = $p['x'] . ',' . $p['y'];
                    }
                    $polyline_str = implode(' ', $polyline_parts);
                ?>
                    <svg viewBox="0 0 <?= $svg_w; ?> <?= $svg_h; ?>" style="width:100%; height:auto; background:#FFF; border-radius:12px; border:1px solid #F2DFDA;">
                        <line x1="<?= $pad_l; ?>" y1="<?= $pad_t + $plot_h; ?>" x2="<?= $svg_w - $pad_r; ?>" y2="<?= $pad_t + $plot_h; ?>" stroke="#E2C8C2" stroke-width="1.5"/>
                        <line x1="<?= $pad_l; ?>" y1="<?= $pad_t; ?>" x2="<?= $pad_l; ?>" y2="<?= $pad_t + $plot_h; ?>" stroke="#E2C8C2" stroke-width="1.5"/>
                        <text x="6" y="<?= $pad_t + 8; ?>" font-size="11" fill="#755B5E"><?= $max_berat; ?>kg</text>
                        <text x="10" y="<?= $pad_t + $plot_h; ?>" font-size="11" fill="#755B5E">0kg</text>
                        <?php if (count($points) > 1): ?>
                            <polyline fill="none" stroke="#D96B78" stroke-width="3" points="<?= $polyline_str; ?>"/>
                        <?php endif; ?>
                        <?php foreach ($points as $pt): ?>
                            <circle cx="<?= $pt['x']; ?>" cy="<?= $pt['y']; ?>" r="5" fill="#D96B78"/>
                            <text x="<?= $pt['x']; ?>" y="<?= $pt['y'] - 8; ?>" font-size="11" font-weight="bold" fill="#3D2C2E" text-anchor="middle"><?= htmlspecialchars((string)$pt['berat']); ?></text>
                            <text x="<?= $pt['x']; ?>" y="<?= $svg_h - 8; ?>" font-size="10" fill="#755B5E" text-anchor="middle"><?= htmlspecialchars((string)$pt['tgl']); ?></text>
                        <?php endforeach; ?>
                    </svg>
                <?php else: ?>
                    <p style="font-size:13px; color:#755B5E;">Belum ada titik riwayat untuk kucing ini.</p>
                <?php endif; ?>

                <div class="saran-box <?= $eval_aktif['kelas']; ?>">
                    <strong>Saran:</strong> <?= htmlspecialchars($eval_aktif['saran']); ?>
                </div>
            </div>

            <div class="history-table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Berat</th>
                            <th>Nafsu Makan</th>
                            <th>Vaksin</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($riwayat_list) as $rw):
                            $jns_rw = trim((string)($rw['jenis_vaksin'] ?? ''));
                            $label_vx = htmlspecialchars((string)$rw['status_vaksin']);
                            if ($jns_rw !== '' && $jns_rw !== '-') {
                                $label_vx .= " (" . htmlspecialchars($jns_rw) . ")";
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$rw['tanggal_catat']); ?></td>
                            <td><strong><?= htmlspecialchars((string)$rw['berat_kg']); ?> kg</strong></td>
                            <td><?= htmlspecialchars((string)$rw['nafsu_makan']); ?></td>
                            <td><?= $label_vx; ?></td>
                            <td><?= htmlspecialchars((string)$rw['catatan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <form method="GET" class="filter-bar">
        <input type="text" name="cari" placeholder="Cari nama atau ras kucing..." value="<?= htmlspecialchars($cari); ?>">
        <select name="vaksin">
            <option value="">Semua Status Vaksin</option>
            <option value="Sudah Vaksin" <?= $filter === 'Sudah Vaksin' ? 'selected' : ''; ?>>Sudah Vaksin</option>
            <option value="Belum Vaksin" <?= $filter === 'Belum Vaksin' ? 'selected' : ''; ?>>Belum Vaksin</option>
        </select>
        <button type="submit" class="btn-filter">Filter</button>
        <?php if ($cari !== '' || $filter !== ''): ?>
            <a href="dashboard.php" class="btn-reset">Reset</a>
        <?php endif; ?>
    </form>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <section class="cat-grid">
            <?php while ($row = mysqli_fetch_assoc($result)):
                $ev = evaluasiKesehatan(
                    $row['usia_bulan'] ?? 0,
                    $row['berat_kg'] ?? 0,
                    $row['nafsu_makan'] ?? 'Baik / Aktif',
                    $row['status_vaksin'] ?? 'Belum Vaksin',
                    $row['jenis_vaksin'] ?? '-'
                );
                $jenis_tampil = trim((string)($row['jenis_vaksin'] ?? '-'));
                if ($jenis_tampil === '') $jenis_tampil = '-';
            ?>
            <article class="cat-card">
                <div>
                    <div class="cat-head">
                        <div class="cat-profile-wrap">
                            <img src="<?= pawly_foto_kucing_src($row['foto_kucing'] ?? '', '../'); ?>"
                                 alt="<?= htmlspecialchars($row['nama_kucing']); ?>"
                                 class="cat-avatar">
                            <div>
                                <div class="cat-name"><?= htmlspecialchars($row['nama_kucing']); ?></div>
                                <div class="cat-breed"><?= htmlspecialchars($row['ras']); ?> &bull; <?= htmlspecialchars($row['jenis_kelamin']); ?></div>
                            </div>
                        </div>
                        <span class="badge <?= $ev['kelas']; ?>"><?= htmlspecialchars($ev['label']); ?></span>
                    </div>

                    <div class="cat-metrics">
                        <div><small>Umur</small><strong><?= (int)$row['usia_bulan']; ?> Bln</strong></div>
                        <div><small>Berat</small><strong><?= number_format((float)$row['berat_kg'], 2); ?> Kg</strong></div>
                        <div><small>Nafsu Makan</small><strong><?= htmlspecialchars($row['nafsu_makan'] ?? 'Baik / Aktif'); ?></strong></div>
                    </div>

                    <p class="cat-note">
                        <strong>Vaksin:</strong> <?= htmlspecialchars($row['status_vaksin']); ?> (<?= htmlspecialchars($jenis_tampil); ?>)<br>
                        <strong>Catatan:</strong> <?= htmlspecialchars($row['catatan_kesehatan']); ?>
                    </p>

                    <div class="card-tip <?= $ev['kelas']; ?>">
                        <strong>Saran:</strong> <?= htmlspecialchars($ev['saran']); ?>
                    </div>
                </div>

                <div class="cat-footer">
                    <span style="font-size:12px; color:#755B5E;">Pantau: <?= htmlspecialchars($row['tgl_pantau'] ?? '-'); ?></span>
                    <div class="aksi">
                        <a href="dashboard.php?kucing_id=<?= (int)$row['id']; ?>" class="grafik">Grafik</a>
                        <a href="edit.php?id=<?= (int)$row['id']; ?>" class="edit">Update</a>
                        <a href="hapus.php?id=<?= (int)$row['id']; ?>" class="hapus" onclick="return confirm('Yakin hapus data kucing ini?')">Hapus</a>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </section>
    <?php else: ?>
        <div style="background:rgba(255,255,255,0.96); border:1px dashed #E58C96; border-radius:20px; padding:32px; text-align:center;">
            <p style="margin-bottom:12px;">Belum ada data kucing yang tersimpan.</p>
            <a href="tambah.php" class="btn-primary">+ Tambah Data Kucing</a>
        </div>
    <?php endif; ?>
</main>

<footer>
    <span><strong>Pawly</strong> &mdash; Jurnal & Pemantauan Kesehatan Kucing</span>
    <span>Panduan vaksin & perawatan bersifat umum; konsultasikan dengan dokter hewan.</span>
</footer>

</body>
</html>
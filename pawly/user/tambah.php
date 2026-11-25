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

$pesan_error = "";

$nama_kucing       = "";
$foto_kucing       = "";
$ras               = "Domestik / Campuran";
$jenis_kelamin     = "Jantan";
$usia_bulan        = "";
$berat_kg          = "";
$nafsu_makan       = "Baik / Aktif";
$status_vaksin     = "Belum Vaksin";
$jenis_vaksin      = "-";
$tgl_pantau        = date('Y-m-d');
$catatan_kesehatan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kucing       = trim($_POST['nama_kucing'] ?? '');
    $ras               = trim($_POST['ras'] ?? 'Domestik / Campuran');
    $jenis_kelamin     = trim($_POST['jenis_kelamin'] ?? 'Jantan');
    $usia_bulan        = (int)($_POST['usia_bulan'] ?? 0);
    $berat_kg          = (float)($_POST['berat_kg'] ?? 0);
    $nafsu_makan       = trim($_POST['nafsu_makan'] ?? 'Baik / Aktif');
    $status_vaksin     = trim($_POST['status_vaksin'] ?? 'Belum Vaksin');
    $jenis_vaksin_raw  = trim($_POST['jenis_vaksin'] ?? '-');
    $tgl_pantau        = trim($_POST['tgl_pantau'] ?? date('Y-m-d'));
    $catatan_kesehatan = trim($_POST['catatan_kesehatan'] ?? '');

    if ($status_vaksin === 'Sudah Vaksin') {
        $jenis_vaksin = ($jenis_vaksin_raw !== '') ? $jenis_vaksin_raw : '-';
    } else {
        $status_vaksin = 'Belum Vaksin';
        $jenis_vaksin  = '-';
    }

    $upload_res = pawly_upload_foto_kucing('foto_kucing', '');

    if (!$upload_res['ok']) {
        $pesan_error = $upload_res['error'];
    } elseif ($nama_kucing === '' || $usia_bulan < 0 || $berat_kg <= 0 || $tgl_pantau === '') {
        $pesan_error = "Nama kucing, umur, berat badan (lebih dari 0), dan tanggal pemantauan wajib diisi!";
    } else {
        $foto_kucing = $upload_res['filename'];

        $nama_sql    = mysqli_real_escape_string($koneksi, $nama_kucing);
        $foto_sql    = mysqli_real_escape_string($koneksi, $foto_kucing);
        $ras_sql     = mysqli_real_escape_string($koneksi, $ras);
        $jk_sql      = mysqli_real_escape_string($koneksi, $jenis_kelamin);
        $nafsu_sql   = mysqli_real_escape_string($koneksi, $nafsu_makan);
        $vaksin_sql  = mysqli_real_escape_string($koneksi, $status_vaksin);
        $jns_vx_sql  = mysqli_real_escape_string($koneksi, $jenis_vaksin);
        $tgl_sql     = mysqli_real_escape_string($koneksi, $tgl_pantau);
        $catatan_sql = mysqli_real_escape_string($koneksi, $catatan_kesehatan);

        $sql_kucing = "INSERT INTO kucing (
            user_id, nama_kucing, foto_kucing, ras, jenis_kelamin, usia_bulan, berat_kg,
            nafsu_makan, status_vaksin, jenis_vaksin, tgl_pantau, catatan_kesehatan
        ) VALUES (
            '$user_id', '$nama_sql', '$foto_sql', '$ras_sql', '$jk_sql', '$usia_bulan', '$berat_kg',
            '$nafsu_sql', '$vaksin_sql', '$jns_vx_sql', '$tgl_sql', '$catatan_sql'
        )";

        if (mysqli_query($koneksi, $sql_kucing)) {
            $kucing_baru_id = (int)mysqli_insert_id($koneksi);

            $sql_riwayat = "INSERT INTO riwayat_pemantauan (
                kucing_id, tanggal_catat, berat_kg, nafsu_makan, status_vaksin, jenis_vaksin, catatan
            ) VALUES (
                '$kucing_baru_id', '$tgl_sql', '$berat_kg', '$nafsu_sql', '$vaksin_sql', '$jns_vx_sql', '$catatan_sql'
            )";
            @mysqli_query($koneksi, $sql_riwayat);

            header("Location: dashboard.php?kucing_id=" . $kucing_baru_id);
            exit;
        } else {
            $pesan_error = "Gagal menyimpan data kucing: " . mysqli_error($koneksi);
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
            backdrop-filter: blur(4px);
        }
        .brand { display: inline-flex; align-items: center; text-decoration: none; }
        .nav-menu { display: flex; gap: 20px; align-items: center; font-size: 14px; font-weight: 600; }
        .nav-menu a { text-decoration: none; color: #5C4649; }
        .nav-menu a.aktif { color: #D96B78; }
        .user-pill { background: #FFEFF1; color: #A63F4C; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .btn-logout { background: #F3E2DD; color: #3D2C2E; padding: 7px 15px; border-radius: 999px; text-decoration: none; font-size: 13px; font-weight: 700; }

        .container { flex: 1; max-width: 660px; width: 100%; margin: 28px auto; padding: 0 20px; }
        .card {
            background: rgba(255, 255, 255, 0.97); border: 1px solid #F3E2DD; border-radius: 22px;
            padding: 26px 24px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }
        h1 { font-size: 22px; margin-bottom: 6px; color: #3A2528; }
        .sub { font-size: 13px; color: #755B5E; margin-bottom: 18px; }

        .error-box {
            background: #FFE4E6; border: 1px solid #FDA4AF; color: #9F1239;
            padding: 11px 14px; border-radius: 12px; font-size: 13px; font-weight: 700;
            margin-bottom: 16px;
        }

        label { display: block; font-size: 13px; font-weight: 700; margin-top: 13px; color: #3D2C2E; }
        input, select, textarea {
            width: 100%; padding: 10px 12px; margin-top: 6px; border: 1px solid #E2C8C2;
            border-radius: 12px; background: #FFFDFC; font-size: 14px; color: #3D2C2E;
        }
        input[type="file"] { padding: 8px 10px; background: #FFF9F7; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #E58C96; }
        textarea { min-height: 85px; resize: vertical; }

        .photo-upload-row {
            display: flex; align-items: center; gap: 14px; margin-top: 8px;
            background: #FFF9F7; border: 1px solid #F3E2DD; border-radius: 14px; padding: 12px;
        }
        .photo-preview {
            width: 68px; height: 68px; border-radius: 16px; object-fit: cover;
            border: 2px solid #F3DCD6; background: #FFF; flex-shrink: 0;
        }
        .photo-help { font-size: 12px; color: #755B5E; margin-top: 4px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 560px) {
            .grid-2 { grid-template-columns: 1fr; }
        }

        .actions { margin-top: 22px; display: flex; gap: 12px; align-items: center; }
        .btn-submit {
            background: #E58C96; color: #FFF; border: none; padding: 11px 22px;
            border-radius: 999px; font-size: 14px; font-weight: 700; cursor: pointer;
        }
        .btn-submit:hover { background: #D96B78; }
        .btn-batal { font-size: 14px; font-weight: 600; color: #755B5E; text-decoration: none; }

        footer {
            background: #FFF2EF; border-top: 1px solid #F1DCD6; padding: 18px 6%;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;
            font-size: 13px; color: #755B5E; margin-top: 36px;
        }
    </style>
</head>
<body class="pawly-bg">

<header class="navbar">
    <a href="../index.php" class="brand"><?= pawly_logo_html(); ?></a>
    <nav class="nav-menu">
        <a href="../index.php">Beranda</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="tambah.php" class="aktif">+ Catat Kucing</a>
    </nav>
    <div style="display:flex; gap:10px; align-items:center;">
        <span class="user-pill">Halo, <?= htmlspecialchars($username); ?></span>
        <a href="../logout.php" class="btn-logout">Keluar</a>
    </div>
</header>

<main class="container">
    <div class="card">
        <h1>Tambah Profil & Catatan Kucing</h1>
        <p class="sub">Lengkapi data dan foto profil kucing agar pemantauan kesehatannya makin personal.</p>

        <?php if ($pesan_error !== ''): ?>
            <div class="error-box"><?= htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="nama_kucing">Nama Kucing</label>
            <input type="text" id="nama_kucing" name="nama_kucing" value="<?= htmlspecialchars($nama_kucing); ?>" placeholder="Contoh: Mochi" required>

            <label for="foto_kucing">Foto Profil Kucing (Opsional)</label>
            <div class="photo-upload-row">
                <img id="previewFoto" src="<?= pawly_foto_kucing_src($foto_kucing, '../'); ?>" alt="Preview Foto Kucing" class="photo-preview">
                <div style="flex:1;">
                    <input type="file" id="foto_kucing" name="foto_kucing" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewGambar(this)">
                    <p class="photo-help">Format JPG, PNG, WEBP, atau GIF (maks. 2 MB). Jika dikosongkan, pakai ikon paw default.</p>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="ras">Jenis / Ras Kucing</label>
                    <select id="ras" name="ras">
                        <?php
                        $opsi_ras = ['Domestik / Campuran', 'Persia', 'British Shorthair', 'Scottish Fold', 'Anggora', 'Bengal', 'Maine Coon'];
                        foreach ($opsi_ras as $r_opt):
                        ?>
                            <option value="<?= htmlspecialchars($r_opt); ?>" <?= ($ras === $r_opt) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($r_opt); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin">
                        <option value="Jantan" <?= ($jenis_kelamin === 'Jantan') ? 'selected' : ''; ?>>Jantan</option>
                        <option value="Betina" <?= ($jenis_kelamin === 'Betina') ? 'selected' : ''; ?>>Betina</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="usia_bulan">Umur Saat Ini (Bulan)</label>
                    <input type="number" id="usia_bulan" name="usia_bulan" min="0" max="300" value="<?= htmlspecialchars((string)$usia_bulan); ?>" placeholder="Contoh: 6" required>
                </div>

                <div>
                    <label for="berat_kg">Berat Badan (Kg)</label>
                    <input type="number" id="berat_kg" name="berat_kg" step="0.01" min="0.1" max="30" value="<?= htmlspecialchars((string)$berat_kg); ?>" placeholder="Contoh: 3.20" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="nafsu_makan">Nafsu Makan & Aktivitas</label>
                    <select id="nafsu_makan" name="nafsu_makan">
                        <option value="Baik / Aktif" <?= ($nafsu_makan === 'Baik / Aktif') ? 'selected' : ''; ?>>Baik / Aktif</option>
                        <option value="Menurun" <?= ($nafsu_makan === 'Menurun') ? 'selected' : ''; ?>>Menurun</option>
                        <option value="Lesu" <?= ($nafsu_makan === 'Lesu') ? 'selected' : ''; ?>>Lesu</option>
                    </select>
                </div>

                <div>
                    <label for="status_vaksin">Status Vaksin</label>
                    <select id="status_vaksin" name="status_vaksin" onchange="toggleJenisVaksin()">
                        <option value="Belum Vaksin" <?= ($status_vaksin === 'Belum Vaksin') ? 'selected' : ''; ?>>Belum Vaksin</option>
                        <option value="Sudah Vaksin" <?= ($status_vaksin === 'Sudah Vaksin') ? 'selected' : ''; ?>>Sudah Vaksin</option>
                    </select>
                </div>
            </div>

            <div id="wrap_jenis_vaksin" style="display:none;">
                <label for="jenis_vaksin">Jenis Vaksin yang Sudah Diberikan</label>
                <select id="jenis_vaksin" name="jenis_vaksin">
                    <option value="-" <?= ($jenis_vaksin === '-' || $jenis_vaksin === '') ? 'selected' : ''; ?>>-- Pilih Jenis Vaksin --</option>
                    <option value="F3 / Tricat" <?= ($jenis_vaksin === 'F3 / Tricat') ? 'selected' : ''; ?>>F3 / Tricat</option>
                    <option value="F4 / Tetracat" <?= ($jenis_vaksin === 'F4 / Tetracat') ? 'selected' : ''; ?>>F4 / Tetracat</option>
                    <option value="Rabies" <?= ($jenis_vaksin === 'Rabies') ? 'selected' : ''; ?>>Rabies</option>
                    <option value="Lengkap / Booster" <?= ($jenis_vaksin === 'Lengkap / Booster') ? 'selected' : ''; ?>>Lengkap / Booster</option>
                </select>
            </div>

            <label for="tgl_pantau">Tanggal Pemantauan</label>
            <input type="date" id="tgl_pantau" name="tgl_pantau" value="<?= htmlspecialchars($tgl_pantau); ?>" required>

            <label for="catatan_kesehatan">Catatan Kesehatan</label>
            <textarea id="catatan_kesehatan" name="catatan_kesehatan" placeholder="Contoh: aktif bermain, pakan cocok, atau catatan kondisi harian..."><?= htmlspecialchars($catatan_kesehatan); ?></textarea>

            <div class="actions">
                <button type="submit" class="btn-submit">Simpan Data Kucing</button>
                <a href="dashboard.php" class="btn-batal">Batal</a>
            </div>
        </form>
    </div>
</main>

<footer>
    <span><strong>Pawly</strong> &mdash; Jurnal & Pemantauan Kesehatan Kucing</span>
    <span>Dashboard &bull; Tambah Kucing</span>
</footer>

<script>
function toggleJenisVaksin() {
    var statusEl = document.getElementById('status_vaksin');
    var wrapEl   = document.getElementById('wrap_jenis_vaksin');
    var jenisEl  = document.getElementById('jenis_vaksin');
    if (!statusEl || !wrapEl) return;

    if (statusEl.value === 'Sudah Vaksin') {
        wrapEl.style.display = 'block';
    } else {
        wrapEl.style.display = 'none';
        if (jenisEl) jenisEl.value = '-';
    }
}

function previewGambar(input) {
    var img = document.getElementById('previewFoto');
    if (!img || !input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

toggleJenisVaksin();
</script>

</body>
</html>
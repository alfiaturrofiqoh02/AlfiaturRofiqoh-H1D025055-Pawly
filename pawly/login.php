<?php
require_once 'koneksi.php';
require_once 'layout_bg.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $pesan_error = "Username dan password wajib diisi!";
    } else {
        $user_aman = mysqli_real_escape_string($koneksi, $username);
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$user_aman' LIMIT 1");

        if ($query && mysqli_num_rows($query) === 1) {
            $user = mysqli_fetch_assoc($query);

            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                header("Location: index.php");
                exit;
            } else {
                $pesan_error = "Password salah!";
            }
        } else {
            $pesan_error = "Username tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Masuk - Pawly</title>
    <?= pawly_bg_style(); ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; display: flex; flex-direction: column; color: #3D2C2E; }

        .navbar {
            background: rgba(255, 255, 255, 0.95); padding: 14px 6%; display: flex;
            justify-content: space-between; align-items: center; border-bottom: 1px solid #F3DCD6;
            backdrop-filter: blur(4px);
        }
        .brand { font-size: 22px; font-weight: 800; color: #D96B78; text-decoration: none; }
        .nav-link { font-size: 14px; font-weight: 600; color: #5C4649; text-decoration: none; }
        .nav-link:hover { color: #D96B78; }

        .wrap {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 32px 20px;
        }
        .box {
            width: 100%; max-width: 420px; background: rgba(255, 255, 255, 0.97);
            padding: 28px 26px; border-radius: 22px; border: 1px solid #F3E2DD;
            box-shadow: 0 10px 26px rgba(180, 110, 120, 0.08);
        }
        h2 { font-size: 24px; margin-bottom: 6px; color: #3A2528; }
        .sub { font-size: 13px; color: #755B5E; margin-bottom: 16px; }

        .warning-box {
            background: #FFE4E6; border: 1px solid #FDA4AF; color: #9F1239;
            padding: 11px 13px; border-radius: 12px; font-size: 13px; font-weight: 700;
            margin-bottom: 14px;
        }

        label { display: block; font-size: 13px; font-weight: 600; margin-top: 12px; }
        input {
            width: 100%; padding: 10px 12px; margin-top: 5px; border: 1px solid #E2C8C2;
            border-radius: 11px; background: #FFFDFC; font-size: 14px;
        }
        input:focus { outline: none; border-color: #E58C96; }

        .btn-login {
            width: 100%; margin-top: 20px; padding: 11px 18px; background: #E58C96;
            color: #FFF; border: none; border-radius: 999px; font-weight: 700;
            font-size: 14px; cursor: pointer;
        }
        .btn-login:hover { background: #D96B78; }

        .foot-note {
            margin-top: 16px; font-size: 13px; color: #755B5E; text-align: center;
        }
        .foot-note a { color: #C95B68; font-weight: 700; text-decoration: none; }

        footer {
            background: #FFF2EF; border-top: 1px solid #F1DCD6; padding: 18px 6%;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;
            font-size: 13px; color: #755B5E;
        }
    </style>
</head>
<body class="pawly-bg">

<header class="navbar">
    <a href="index.php" class="brand">Pawly</a>
    <a href="index.php" class="nav-link">&larr; Kembali ke Beranda</a>
</header>

<main class="wrap">
    <div class="box">
        <h2>Masuk ke Pawly</h2>
        <p class="sub">Silakan login untuk melanjutkan ke beranda dan memantau kesehatan kucingmu.</p>

        <?php if ($pesan_error !== ''): ?>
            <div class="warning-box"><?= htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>

            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <p class="foot-note">
            Belum punya akun? <a href="register.php">Daftar dulu di sini</a>
        </p>
    </div>
</main>

<footer>
    <span><strong>Pawly</strong> &mdash; Catat & Pantau Kesehatan Kucing</span>
    <span>Masuk &bull; Beranda &bull; Dashboard</span>
</footer>

</body>
</html>
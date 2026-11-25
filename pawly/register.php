<?php
require_once 'koneksi.php';

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi']);

    if ($username === "" || $password === "") {
        $pesan = "Username dan password wajib diisi.";
    } elseif ($password !== $konfirmasi) {
        $pesan = "Konfirmasi password tidak cocok.";
    } else {
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek) > 0) {
            $pesan = "Username sudah terdaftar, coba gunakan nama lain.";
        } else {
            // Akun baru otomatis mendapat role 'user'
            $simpan = mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')");
            if ($simpan) {
                header("Location: login.php?daftar=berhasil");
                exit;
            } else {
                $pesan = "Gagal mendaftar akun.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Pawly</title>
    <style>
        body { background: #FFF8F5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .box { background: #FFF; padding: 28px; border-radius: 20px; width: 100%; max-width: 380px; border: 1px solid #F3E2DD; }
        h2 { margin-bottom: 16px; color: #3D2C2E; }
        label { display: block; font-size: 13px; font-weight: 600; margin-top: 12px; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #E2C8C2; border-radius: 10px; }
        button { width: 100%; margin-top: 18px; padding: 11px; background: #E58C96; color: #FFF; border: none; border-radius: 999px; font-weight: 700; cursor: pointer; }
        .error { background: #FFE4E6; color: #9F1239; padding: 10px; border-radius: 10px; font-size: 13px; margin-bottom: 12px; }
        .link { text-align: center; margin-top: 14px; font-size: 13px; }
        .link a { color: #C95B68; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
<div class="box">
    <h2>Daftar Akun Pawly</h2>
    <?php if ($pesan !== ""): ?><div class="error"><?= $pesan; ?></div><?php endif; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="konfirmasi" required>

        <button type="submit">Daftar Akun</button>
    </form>
    <div class="link">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
</div>
</body>
</html>
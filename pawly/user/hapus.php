<?php
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id      = (int)($_GET['id'] ?? 0);

mysqli_query($koneksi, "DELETE FROM kucing WHERE id = '$id' AND user_id = '$user_id'");
header("Location: dashboard.php");
exit;
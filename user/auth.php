<?php
// user/auth.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!function_exists('url')) {
  function url(string $path): string {
    $base = rtrim(BASE_URL ?? '', '/');
    return $base . $path;
  }
}

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: ' . url('/user/login.php'));
  exit;
}

// Ambil data user yang login untuk navbar/ucapan
$pdo = pdo();
$stmt = $pdo->prepare("SELECT id, name, nik, email FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$warga = $stmt->fetch();

if (!$warga) {
  session_destroy();
  header('Location: ' . url('/user/login.php'));
  exit;
}

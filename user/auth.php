<?php
// user/auth.php (FINAL)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

/* ---------- URL helper ---------- */
if (!function_exists('url')) {
  function url(string $path = '/'): string {
    // BASE_URL didefinisikan di config.php, contoh: http://localhost/rt005_portal
    $base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
    $path = '/' . ltrim($path, '/');
    return $base . $path;
  }
}

/* ---------- Session & guard login ---------- */
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
  header('Location: ' . url('/user/login.php'));
  exit;
}

/* ---------- Ambil user terbaru dari DB ---------- */
$pdo = pdo();
$stmt = $pdo->prepare(
  'SELECT id, name, nik, email, rt, rw, address, status, avatar_path, created_at
     FROM users WHERE id = ? LIMIT 1'
);
$stmt->execute([$_SESSION['user_id']]);
$warga = $stmt->fetch();

if (!$warga) {
  session_destroy();
  header('Location: ' . url('/user/login.php'));
  exit;
}

/* ---------- Bangun avatar_url absolut + cache-busting ---------- */
$warga['avatar_url'] = null;

if (!empty($warga['avatar_path'])) {
  // Simpan di DB sebagai path relatif, contoh: "uploads/avatars/UID_xxx.jpg"
  $rel = '/' . ltrim($warga['avatar_path'], '/');

  // Lokasi file fisik (htdocs/rt005_portal + /uploads/avatars/...)
  $fsPath = dirname(__DIR__) . $rel; // __DIR__ = /user → naik satu level ke root project

  // Pakai filemtime sebagai versi agar tembus cache setelah update foto
  $ver = (is_file($fsPath)) ? filemtime($fsPath) : time();
  $warga['avatar_url'] = url($rel) . '?v=' . $ver;
}

/* ---------- Convenience: simpan nama di session (opsional) ---------- */
$_SESSION['user_name'] = $warga['name'];

/* ---------- Utility untuk inisial ---------- */
if (!function_exists('user_initials')) {
  function user_initials($name) {
    $name = trim($name ?: 'Kiki');
    $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 2) {
      return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }
    return mb_strtoupper(mb_substr($name, 0, 1));
  }
}

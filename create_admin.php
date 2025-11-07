<?php
require_once __DIR__ . '/db.php';

$pdo = pdo();

// Ganti username / password sesuai keinginan
$username = 'admin';
$plain_pass = 'admin123';
$name = 'Admin RT005';

// cek apakah sudah ada
$stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "Admin '$username' sudah ada. Hapus dulu atau ganti username.\n";
    exit;
}

$hash = password_hash($plain_pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO admins (username, password, name) VALUES (?, ?, ?)");
$stmt->execute([$username, $hash, $name]);

echo "Admin created: username='$username' password='$plain_pass'. Jangan lupa hapus atau ubah file create_admin.php setelah selesai.";

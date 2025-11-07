<?php
// Pastikan koneksi & session sudah di-load dari db.php/config.php
require_once __DIR__ . '/../db.php';

// Hanya start session jika belum aktif (aman dipakai jika suatu saat pindah logic)
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// Proteksi halaman admin
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

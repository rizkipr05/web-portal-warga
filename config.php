<?php
// config.php
// Ubah sesuai environment XAMPP
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'rt005_portal');
define('DB_USER', 'root');
define('DB_PASS', ''); // kosong untuk XAMPP default
define('BASE_URL', '/rt005_portal'); // sesuaikan jika pakai subfolder
session_start();

if (!defined('ADMIN_USER_ID_FALLBACK')) {
    define('ADMIN_USER_ID_FALLBACK', 1);
  }
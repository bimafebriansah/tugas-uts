<?php
/**
 * Konfigurasi koneksi database (MySQLi)
 * ------------------------------------------------------------
 * Untuk XAMPP lokal: host=localhost, user=root, pass=''
 * Untuk InfinityFree : ganti sesuai kredensial hosting Anda.
 */

// ---- Mulai session sekali saja ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Parameter koneksi ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_marketplace_ayam');

// ---- Nama aplikasi & base URL ----
define('APP_NAME', 'BangkokFarm');
define('APP_TAGLINE', 'Sentra Ayam Bangkok & Ternak Unggulan');
define('APP_WA', '6281288997766');
define('APP_WA_DISPLAY', '0812-8899-7766');
define('BASE_URL', '/web_penjualanAyam');

// ---- Buat koneksi MySQLi ----
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($koneksi->connect_errno) {
    die('Koneksi database gagal: ' . $koneksi->connect_error);
}

// ---- Set charset agar mendukung karakter UTF-8 ----
$koneksi->set_charset('utf8mb4');

// ---- Pastikan data kategori & produk termigrasi ke Ayam Bangkok ----
require_once __DIR__ . '/../database/auto_migrate.php';

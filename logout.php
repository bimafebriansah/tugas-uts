<?php
require_once __DIR__ . '/includes/fungsi.php';

// Hapus data login namun pertahankan session untuk menampilkan pesan flash
$_SESSION = [];
set_flash('success', 'Anda telah logout.');
redirect(BASE_URL . '/index.php');

<?php
require_once __DIR__ . '/../includes/fungsi.php';

wajib_role(['admin']);
$u          = user_login();
$id_user    = (int) $u['id_user'];
$is_penjual = false;

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID produk tidak valid.');
    redirect(BASE_URL . '/kelola/produk.php');
}

// Penjual hanya boleh menghapus produk miliknya sendiri
if ($is_penjual) {
    $stmt = $koneksi->prepare('DELETE FROM produk WHERE id_produk = ? AND id_penjual = ?');
    $stmt->bind_param('ii', $id, $id_user);
} else {
    $stmt = $koneksi->prepare('DELETE FROM produk WHERE id_produk = ?');
    $stmt->bind_param('i', $id);
}
$stmt->execute();
$terpengaruh = $stmt->affected_rows;
$stmt->close();

if ($terpengaruh > 0) {
    set_flash('success', 'Produk berhasil dihapus.');
} else {
    set_flash('danger', 'Produk tidak ditemukan atau bukan milik Anda.');
}
redirect(BASE_URL . '/kelola/produk.php');

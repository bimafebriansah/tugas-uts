<?php
/**
 * Penanganan aksi keranjang belanja:
 *   tambah | ubah | hapus | kosongkan
 */
require_once __DIR__ . '/includes/fungsi.php';

wajib_login();
$id_user = (int) $_SESSION['user']['id_user'];
$aksi    = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

switch ($aksi) {
    case 'tambah':
        $id_produk = (int) ($_POST['id_produk'] ?? 0);
        $qty       = max(1, (int) ($_POST['qty'] ?? 1));

        // Ambil data produk & stok
        $stmt = $koneksi->prepare('SELECT nama_produk, stok FROM produk WHERE id_produk = ?');
        $stmt->bind_param('i', $id_produk);
        $stmt->execute();
        $produk = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$produk) {
            set_flash('danger', 'Produk tidak ditemukan.');
            break;
        }
        if ((int) $produk['stok'] <= 0) {
            set_flash('danger', 'Stok produk habis.');
            break;
        }

        // Cek apakah produk sudah ada di keranjang
        $stmt = $koneksi->prepare('SELECT id_keranjang, qty FROM keranjang WHERE id_user = ? AND id_produk = ?');
        $stmt->bind_param('ii', $id_user, $id_produk);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stok = (int) $produk['stok'];
        if ($item) {
            $qty_baru = min($stok, (int) $item['qty'] + $qty);
            $stmt = $koneksi->prepare('UPDATE keranjang SET qty = ? WHERE id_keranjang = ?');
            $stmt->bind_param('ii', $qty_baru, $item['id_keranjang']);
            $stmt->execute();
            $stmt->close();
        } else {
            $qty = min($stok, $qty);
            $stmt = $koneksi->prepare('INSERT INTO keranjang (id_user, id_produk, qty) VALUES (?, ?, ?)');
            $stmt->bind_param('iii', $id_user, $id_produk, $qty);
            $stmt->execute();
            $stmt->close();
        }
        set_flash('success', 'Produk "' . $produk['nama_produk'] . '" ditambahkan ke keranjang.');
        break;

    case 'ubah':
        $id_keranjang = (int) ($_POST['id_keranjang'] ?? 0);
        $qty          = (int) ($_POST['qty'] ?? 1);

        // Pastikan item milik user & ambil stok
        $stmt = $koneksi->prepare('SELECT k.id_keranjang, p.stok
                                   FROM keranjang k JOIN produk p ON p.id_produk = k.id_produk
                                   WHERE k.id_keranjang = ? AND k.id_user = ?');
        $stmt->bind_param('ii', $id_keranjang, $id_user);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$item) {
            set_flash('danger', 'Item keranjang tidak ditemukan.');
            break;
        }
        $qty = max(1, min((int) $item['stok'], $qty));
        $stmt = $koneksi->prepare('UPDATE keranjang SET qty = ? WHERE id_keranjang = ?');
        $stmt->bind_param('ii', $qty, $id_keranjang);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Jumlah item diperbarui.');
        break;

    case 'hapus':
        $id_keranjang = (int) ($_GET['id'] ?? $_POST['id_keranjang'] ?? 0);
        $stmt = $koneksi->prepare('DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?');
        $stmt->bind_param('ii', $id_keranjang, $id_user);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Item dihapus dari keranjang.');
        break;

    case 'kosongkan':
        $stmt = $koneksi->prepare('DELETE FROM keranjang WHERE id_user = ?');
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Keranjang dikosongkan.');
        break;

    default:
        set_flash('danger', 'Aksi tidak dikenali.');
}

redirect(BASE_URL . '/keranjang.php');

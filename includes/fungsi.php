<?php
/**
 * Kumpulan fungsi bantuan (helper) aplikasi marketplace.
 */

require_once __DIR__ . '/../config/koneksi.php';

/** Format angka menjadi Rupiah, contoh: 35000 -> Rp 35.000 */
function rupiah($angka)
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/** Amankan output agar terhindar dari XSS */
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/** Redirect ke halaman lain lalu hentikan script */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/** Set notifikasi flash (contoh: pesan berhasil / gagal) */
function set_flash($tipe, $pesan)
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/** Tampilkan notifikasi flash lalu hapus dari session */
function tampilkan_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['tipe']) . ' alert-dismissible fade show" role="alert">'
            . e($f['pesan'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

/** Cek apakah pengguna sudah login */
function sudah_login()
{
    return isset($_SESSION['user']);
}

/** Data pengguna yang sedang login */
function user_login()
{
    return $_SESSION['user'] ?? null;
}

/** Cek peran pengguna yang sedang login */
function punya_role($role)
{
    return sudah_login() && $_SESSION['user']['role'] === $role;
}

/** Wajib login; jika belum, arahkan ke halaman login */
function wajib_login()
{
    if (!sudah_login()) {
        set_flash('warning', 'Silakan login terlebih dahulu.');
        redirect(BASE_URL . '/login.php');
    }
}

/** Wajib memiliki salah satu peran tertentu (admin) */
function wajib_role(array $roles)
{
    wajib_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        set_flash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        redirect(BASE_URL . '/index.php');
    }
}

/** Ambil URL gambar produk: gambar unggahan lokal atau URL eksternal */
function gambar_produk($gambar)
{
    if (empty($gambar)) {
        return 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=600&q=80';
    }
    if (preg_match('#^https?://#i', $gambar)) {
        return $gambar;
    }
    return BASE_URL . '/assets/img/produk/' . rawurlencode($gambar);
}

/** Tampilkan badge status pesanan modern */
function status_pesanan_badge($status)
{
    $map = [
        'pending'  => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', 'icon' => 'bi-hourglass-split'],
        'diproses' => ['label' => 'Sedang Diproses/Karantina', 'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle', 'icon' => 'bi-gear-wide-connected'],
        'dikirim'  => ['label' => 'Dalam Pengiriman Kargo', 'class' => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle', 'icon' => 'bi-truck'],
        'selesai'  => ['label' => 'Selesai / Tiba', 'class' => 'bg-success-subtle text-success-emphasis border border-success-subtle', 'icon' => 'bi-check-circle-fill'],
        'batal'    => ['label' => 'Dibatalkan', 'class' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle', 'icon' => 'bi-x-circle-fill'],
    ];

    $cfg = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-secondary-subtle text-secondary-emphasis', 'icon' => 'bi-info-circle'];
    return '<span class="badge rounded-pill px-3 py-2 ' . $cfg['class'] . '"><i class="bi ' . $cfg['icon'] . ' me-1"></i>' . e($cfg['label']) . '</span>';
}

/** Generate link WhatsApp */
function link_whatsapp($pesan = '')
{
    $nomor = defined('APP_WA') ? APP_WA : '6281288997766';
    return 'https://wa.me/' . $nomor . '?text=' . rawurlencode($pesan);
}

/** Hitung jumlah item di keranjang pengguna (untuk badge navbar) */
function jumlah_keranjang(mysqli $koneksi)
{
    if (!sudah_login()) {
        return 0;
    }
    $id = (int) $_SESSION['user']['id_user'];
    $stmt = $koneksi->prepare('SELECT COALESCE(SUM(qty),0) AS total FROM keranjang WHERE id_user = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($res['total'] ?? 0);
}

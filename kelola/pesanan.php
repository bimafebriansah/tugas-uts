<?php
require_once __DIR__ . '/../includes/fungsi.php';

wajib_role(['admin']);
$u          = user_login();
$id_user    = (int) $u['id_user'];
$is_penjual = false;

$status_valid = ['pending', 'diproses', 'dikirim', 'selesai', 'batal'];

// ---- Ubah status pesanan ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ubah_status') {
    $id_pesanan = (int) ($_POST['id_pesanan'] ?? 0);
    $status     = $_POST['status'] ?? '';

    if (!in_array($status, $status_valid, true)) {
        set_flash('danger', 'Status pesanan tidak valid.');
        redirect(BASE_URL . '/kelola/pesanan.php');
    }

    $stmt = $koneksi->prepare('UPDATE pesanan SET status = ? WHERE id_pesanan = ?');
    $stmt->bind_param('si', $status, $id_pesanan);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Status pesanan #' . $id_pesanan . ' berhasil diubah menjadi "' . ucfirst($status) . '".');
    redirect(BASE_URL . '/kelola/pesanan.php#pesanan-' . $id_pesanan);
}

// ---- Daftar pesanan ----
$stmt = $koneksi->prepare('SELECT ps.*, u.nama AS nama_pembeli, u.email AS email_pembeli
                           FROM pesanan ps JOIN users u ON u.id_user = ps.id_pembeli
                           ORDER BY ps.tanggal DESC');
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$judul = 'Kelola Pesanan & Kargo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-receipt-cutoff text-success me-2"></i> Kelola Transaksi &amp; Pengiriman Kargo</h3>
        <p class="text-muted small mb-0">Pantau verifikasi pembayaran, persiapan boks karantina, dan nomor resi kargo.</p>
    </div>
    <a href="<?= BASE_URL ?>/kelola/index.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>
</div>

<?php if (empty($daftar)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
        <i class="bi bi-inbox text-muted mb-3" style="font-size: 3.5rem;"></i>
        <h4 class="fw-bold">Belum Ada Pesanan Masuk</h4>
        <p class="text-muted mb-0">Semua pesanan baru yang dibuat pembeli akan muncul di halaman ini.</p>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($daftar as $ps): ?>
            <?php
            $stmt = $koneksi->prepare('SELECT d.*, p.nama_produk, p.gambar FROM detail_pesanan d
                                       JOIN produk p ON p.id_produk = d.id_produk WHERE d.id_pesanan = ?');
            $stmt->bind_param('i', $ps['id_pesanan']);
            $stmt->execute();
            $detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            ?>
            <div class="col-12" id="pesanan-<?= (int)$ps['id_pesanan'] ?>">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-light py-3 px-4 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-5 fw-bold text-dark">Invoice #<?= (int) $ps['id_pesanan'] ?></span>
                            <span class="text-muted small">&middot; <?= date('d M Y, H:i', strtotime($ps['tanggal'])) ?> WIB</span>
                            <span class="text-muted small">&middot; Akun: <strong><?= e($ps['nama_pembeli']) ?></strong></span>
                        </div>
                        <div>
                            <?= status_pesanan_badge($ps['status']) ?>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Items Ordered -->
                            <div class="col-lg-7 border-end-lg">
                                <h6 class="fw-bold text-dark mb-2">Item Ayam / Produk Dipesan:</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm align-middle">
                                        <thead class="table-light small">
                                            <tr>
                                                <th>Produk</th>
                                                <th class="text-end">Harga</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($detail as $d): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="<?= e(gambar_produk($d['gambar'])) ?>" width="36" height="36" style="object-fit:cover" class="rounded">
                                                        <span class="fw-semibold text-dark small"><?= e($d['nama_produk']) ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-end small text-muted"><?= rupiah($d['harga_satuan']) ?></td>
                                                <td class="text-center small fw-bold"><?= (int)$d['qty'] ?></td>
                                                <td class="text-end small fw-semibold text-dark"><?= rupiah($d['subtotal']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3" class="text-end small">Total Tagihan:</th>
                                                <th class="text-end harga fs-5 text-success"><?= rupiah($ps['total']) ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Destination & Status Management -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Tujuan Pengiriman Kargo:</h6>
                                <div class="p-3 bg-light rounded-3 small mb-3">
                                    <div class="fw-bold text-dark"><?= e($ps['nama_penerima']) ?></div>
                                    <div class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i> <?= e($ps['telepon']) ?></div>
                                    <div class="mt-1 text-secondary"><?= nl2br(e($ps['alamat_kirim'])) ?></div>
                                </div>

                                <!-- Update Status Form -->
                                <form method="post" class="p-3 border rounded-3 bg-white">
                                    <input type="hidden" name="aksi" value="ubah_status">
                                    <input type="hidden" name="id_pesanan" value="<?= (int) $ps['id_pesanan'] ?>">
                                    
                                    <label class="form-label fw-semibold small text-dark mb-1">Ubah Status Pesanan:</label>
                                    <div class="input-group mb-2">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="pending" <?= $ps['status'] === 'pending' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                            <option value="diproses" <?= $ps['status'] === 'diproses' ? 'selected' : '' ?>>Sedang Diproses/Karantina</option>
                                            <option value="dikirim" <?= $ps['status'] === 'dikirim' ? 'selected' : '' ?>>Dalam Pengiriman Kargo</option>
                                            <option value="selesai" <?= $ps['status'] === 'selesai' ? 'selected' : '' ?>>Selesai / Sudah Diterima</option>
                                            <option value="batal" <?= $ps['status'] === 'batal' ? 'selected' : '' ?>>Batalkan Pesanan</option>
                                        </select>
                                        <button class="btn btn-sm btn-farm-primary" type="submit">
                                            <i class="bi bi-check-lg"></i> Simpan
                                        </button>
                                    </div>
                                </form>

                                <!-- Contact Buyer on WhatsApp -->
                                <?php if (!empty($ps['telepon'])): ?>
                                    <?php
                                    $wa_target = preg_replace('/[^0-9]/', '', $ps['telepon']);
                                    if (substr($wa_target, 0, 1) === '0') {
                                        $wa_target = '62' . substr($wa_target, 1);
                                    }
                                    ?>
                                    <a href="https://wa.me/<?= $wa_target ?>?text=<?= rawurlencode('Halo ' . $ps['nama_penerima'] . ', ini dari Admin BangkokFarm terkait pesanan #' . $ps['id_pesanan'] . '...') ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-success btn-sm w-100 mt-2">
                                        <i class="bi bi-whatsapp me-1"></i> Hubungi Pembeli via WhatsApp
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

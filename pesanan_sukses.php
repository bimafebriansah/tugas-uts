<?php
require_once __DIR__ . '/includes/fungsi.php';

wajib_login();
$id       = (int) ($_GET['id'] ?? 0);
$id_user  = (int) $_SESSION['user']['id_user'];
$u        = user_login();

// Ambil header pesanan (pembeli hanya boleh melihat pesanannya sendiri)
$sql = 'SELECT ps.*, u.nama AS nama_pembeli FROM pesanan ps
        JOIN users u ON u.id_user = ps.id_pembeli
        WHERE ps.id_pesanan = ?';
if ($u['role'] === 'user') {
    $sql .= ' AND ps.id_pembeli = ' . $id_user;
}
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect(BASE_URL . '/index.php');
}

$stmt = $koneksi->prepare('SELECT d.*, p.nama_produk, p.gambar FROM detail_pesanan d
                           JOIN produk p ON p.id_produk = d.id_produk
                           WHERE d.id_pesanan = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$judul = 'Pesanan Berhasil #' . $pesanan['id_pesanan'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Stepper -->
<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-3 rounded-4 bg-white">
            <div class="d-flex justify-content-between align-items-center text-center">
                <div class="flex-fill">
                    <span class="badge bg-success bg-opacity-25 text-success rounded-circle p-2 px-3 fw-bold mb-1">1</span>
                    <div class="small text-muted">Keranjang Belanja</div>
                </div>
                <div class="flex-fill border-top border-2 border-success"></div>
                <div class="flex-fill">
                    <span class="badge bg-success bg-opacity-25 text-success rounded-circle p-2 px-3 fw-bold mb-1">2</span>
                    <div class="small text-muted">Data Pengiriman Kargo</div>
                </div>
                <div class="flex-fill border-top border-2 border-success"></div>
                <div class="flex-fill">
                    <span class="badge bg-success rounded-circle p-2 px-3 fw-bold mb-1">3</span>
                    <div class="small fw-bold text-success">Pesanan Berhasil</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mb-4">
    <div class="brand-icon-wrapper bg-success text-white mx-auto mb-2" style="width: 70px; height: 70px; font-size: 2.2rem; border-radius: 50%;">
        <i class="bi bi-check-lg"></i>
    </div>
    <h3 class="fw-bold mt-2 text-dark">Pesanan Anda Berhasil Dibuat!</h3>
    <p class="text-muted">Nomor Invoice Pesanan: <strong class="text-success fs-5">#<?= (int) $pesanan['id_pesanan'] ?></strong></p>
</div>

<div class="row justify-content-center mb-5">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-success bg-opacity-10 py-3 px-4 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="text-muted small">Waktu Transaksi:</span>
                    <div class="fw-semibold text-dark"><?= date('d F Y, H:i', strtotime($pesanan['tanggal'])) ?> WIB</div>
                </div>
                <div>
                    <?= status_pesanan_badge($pesanan['status']) ?>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-person-fill text-success me-1"></i> Data Penerima:</h6>
                        <div class="fw-bold text-dark"><?= e($pesanan['nama_penerima']) ?></div>
                        <div class="text-muted small mb-2"><i class="bi bi-telephone-fill me-1"></i> <?= e($pesanan['telepon']) ?></div>
                        <div class="text-secondary small bg-light p-2 rounded-3">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= nl2br(e($pesanan['alamat_kirim'])) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-credit-card-fill text-success me-1"></i> Rekening Resmi Farm:</h6>
                        <div class="p-2 bg-light rounded-3 small">
                            <p class="mb-1"><strong>Bank BCA:</strong> <code>873-091-2345</code> (a.n BangkokFarm Indonesia)</p>
                            <p class="mb-1"><strong>Bank Mandiri:</strong> <code>132-00-9876543-1</code> (a.n BangkokFarm Indonesia)</p>
                            <p class="mb-0 text-muted" style="font-size:0.75rem">*Sertakan nomor pesanan #<?= (int)$pesanan['id_pesanan'] ?> pada berita transfer.</p>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2">Item Ayam / Produk yang Dipesan:</h6>
                <div class="table-responsive mb-3">
                    <table class="table align-middle">
                        <thead class="table-light">
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
                                        <img src="<?= e(gambar_produk($d['gambar'])) ?>" width="40" height="40" style="object-fit:cover" class="rounded">
                                        <span class="fw-semibold text-dark"><?= e($d['nama_produk']) ?></span>
                                    </div>
                                </td>
                                <td class="text-end text-muted small"><?= rupiah($d['harga_satuan']) ?></td>
                                <td class="text-center fw-bold"><?= (int) $d['qty'] ?></td>
                                <td class="text-end fw-semibold text-dark"><?= rupiah($d['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total Pembayaran:</th>
                                <th class="text-end harga fs-5 text-success"><?= rupiah($pesanan['total']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2">
                    <a href="<?= link_whatsapp('Halo Admin BangkokFarm, saya ingin konfirmasi pesanan #' . $pesanan['id_pesanan'] . ' atas nama ' . $pesanan['nama_penerima'] . ' dengan total ' . rupiah($pesanan['total']) . '. Mohon diproses pengiriman kargonya.') ?>" 
                       target="_blank" 
                       class="btn btn-wa fw-bold px-4 py-2 flex-grow-1">
                        <i class="bi bi-whatsapp me-2 fs-5"></i> Konfirmasi Pembayaran ke WhatsApp Admin
                    </a>
                    <a href="<?= BASE_URL ?>/riwayat.php" class="btn btn-outline-dark px-3 py-2">
                        <i class="bi bi-clock-history me-1"></i> Riwayat Pesanan
                    </a>
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-light px-3 py-2 border">
                        <i class="bi bi-shop me-1"></i> Belanja Lagi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

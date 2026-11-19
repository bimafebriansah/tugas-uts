<?php
require_once __DIR__ . '/includes/fungsi.php';

wajib_login();
$id_user = (int) $_SESSION['user']['id_user'];

$stmt = $koneksi->prepare('SELECT k.id_keranjang, k.qty, p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar, u.nama AS nama_penjual
                           FROM keranjang k
                           JOIN produk p ON p.id_produk = k.id_produk
                           JOIN users u  ON u.id_user   = p.id_penjual
                           WHERE k.id_user = ?
                           ORDER BY k.id_keranjang DESC');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($items as $it) {
    $total += $it['harga'] * $it['qty'];
}

$judul = 'Keranjang Belanja';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Checkout Step Indicator -->
<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-3 rounded-4 bg-white">
            <div class="d-flex justify-content-between align-items-center text-center">
                <div class="flex-fill">
                    <span class="badge bg-success rounded-circle p-2 px-3 fw-bold mb-1">1</span>
                    <div class="small fw-bold text-success">Keranjang Belanja</div>
                </div>
                <div class="flex-fill border-top border-2 border-success"></div>
                <div class="flex-fill">
                    <span class="badge bg-secondary bg-opacity-25 text-dark rounded-circle p-2 px-3 fw-bold mb-1">2</span>
                    <div class="small text-muted">Data Pengiriman Kargo</div>
                </div>
                <div class="flex-fill border-top border-2 border-secondary border-opacity-25"></div>
                <div class="flex-fill">
                    <span class="badge bg-secondary bg-opacity-25 text-dark rounded-circle p-2 px-3 fw-bold mb-1">3</span>
                    <div class="small text-muted">Pesanan Selesai</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-bag-check-fill text-success me-2"></i> Keranjang Belanja Anda</h3>
    <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none text-success fw-semibold small">
        <i class="bi bi-arrow-left me-1"></i> Lanjut Pilih Ayam
    </a>
</div>

<?php if (empty($items)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
        <i class="bi bi-cart-x text-muted mb-3" style="font-size: 3.5rem;"></i>
        <h4 class="fw-bold">Keranjang Belanja Masih Kosong</h4>
        <p class="text-muted mb-4">Anda belum memasukkan ayam ternak atau produk apapun ke dalam keranjang belanja.</p>
        <div>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-farm-primary px-4 py-2 rounded-pill fw-semibold">
                <i class="bi bi-grid-fill me-1"></i> Jelajahi Katalog Ayam Bangkok &amp; Ternak
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3">Produk Ternak</th>
                                <th class="py-3">Harga Satuan</th>
                                <th class="text-center py-3">Jumlah (Qty)</th>
                                <th class="py-3">Subtotal</th>
                                <th class="pe-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= e(gambar_produk($it['gambar'])) ?>" width="64" height="64" style="object-fit:cover" class="rounded-3 shadow-sm" alt="<?= e($it['nama_produk']) ?>">
                                        <div>
                                            <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $it['id_produk'] ?>" class="fw-bold text-dark text-decoration-none d-block">
                                                <?= e($it['nama_produk']) ?>
                                            </a>
                                            <span class="small text-muted"><i class="bi bi-shop me-1 text-success"></i> <?= e($it['nama_penjual']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 fw-semibold text-secondary">
                                    <?= rupiah($it['harga']) ?>
                                </td>
                                <td class="text-center py-3" style="min-width:140px">
                                    <form action="<?= BASE_URL ?>/aksi_keranjang.php" method="post" class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="hidden" name="aksi" value="ubah">
                                        <input type="hidden" name="id_keranjang" value="<?= (int) $it['id_keranjang'] ?>">
                                        <input type="number" name="qty" class="form-control form-control-sm text-center fw-bold" style="width:65px" value="<?= (int) $it['qty'] ?>" min="1" max="<?= (int) $it['stok'] ?>">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit" title="Perbarui Jumlah">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                    <small class="text-muted d-block mt-1" style="font-size:0.75rem">Maks: <?= (int)$it['stok'] ?></small>
                                </td>
                                <td class="py-3 fw-bold text-success">
                                    <?= rupiah($it['harga'] * $it['qty']) ?>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <a href="<?= BASE_URL ?>/aksi_keranjang.php?aksi=hapus&id=<?= (int) $it['id_keranjang'] ?>"
                                       class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2" 
                                       title="Hapus dari Keranjang"
                                       onclick="return confirm('Hapus <?= e(addslashes($it['nama_produk'])) ?> dari keranjang?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Petunjuk Pengiriman Hewan -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-start gap-3">
                    <div class="brand-icon-wrapper bg-success bg-opacity-10 text-success" style="width:42px;height:42px;font-size:1.3rem">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="small">
                        <h6 class="fw-bold mb-1 text-dark">Informasi Khusus Pengiriman Ayam Hidup:</h6>
                        <p class="text-muted mb-0">
                            Pesanan ayam akan dipacking menggunakan boks kayu kargo khusus berlubang ventilasi dan diberi bekal jagung basah/air agar ayam tetap prima sampai tujuan. Pengiriman wilayah Pulau Jawa menggunakan jalur KIB / Kalog / Bus Safari resmi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Ringkasan Belanja</h5>
                
                <div class="d-flex justify-content-between mb-2 small text-secondary">
                    <span>Total Barang (<?= count($items) ?> item):</span>
                    <span class="fw-semibold text-dark"><?= rupiah($total) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-secondary">
                    <span>Biaya Box Packing Hewan:</span>
                    <span class="text-success fw-semibold"><i class="bi bi-gift-fill me-1"></i> Gratis (Standar Farm)</span>
                </div>
                <div class="d-flex justify-content-between mb-3 small text-secondary">
                    <span>Ongkir Kargo Hewan:</span>
                    <span class="text-muted">Dihitung saat konfirmasi</span>
                </div>

                <div class="border-top pt-3 mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-muted d-block">Total Pembayaran:</span>
                        <span class="harga fs-3 text-success"><?= rupiah($total) ?></span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-farm-primary py-3 fw-bold rounded-3">
                        <i class="bi bi-shield-check me-1"></i> Lanjut ke Data Pengiriman
                    </a>
                    <a href="<?= link_whatsapp('Halo BangkokFarm, saya sedang checkout keranjang dan ingin konfirmasi rute kargo untuk kota saya...') ?>" 
                       target="_blank" 
                       class="btn btn-outline-success py-2 fw-semibold rounded-3 small">
                        <i class="bi bi-whatsapp me-1"></i> Tanya Ongkir Kargo via WhatsApp
                    </a>
                </div>

                <div class="text-center mt-3 small text-muted">
                    <i class="bi bi-lock-fill me-1 text-success"></i> Transaksi Terenkripsi &amp; Aman
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

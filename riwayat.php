<?php
require_once __DIR__ . '/includes/fungsi.php';

wajib_login();
$id_user = (int) $_SESSION['user']['id_user'];

// Daftar pesanan milik pembeli
$stmt = $koneksi->prepare('SELECT * FROM pesanan WHERE id_pembeli = ? ORDER BY tanggal DESC');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$judul = 'Pesanan Saya';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-clock-history text-success me-2"></i> Riwayat Pesanan Saya</h3>
        <p class="text-muted small mb-0">Pantau status pesanan, pembayaran, dan resi pengiriman kargo ayam Anda.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-farm-primary btn-sm px-3 rounded-pill">
        <i class="bi bi-plus-circle me-1"></i> Beli Ayam Lain
    </a>
</div>

<?php if (empty($daftar)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
        <i class="bi bi-receipt text-muted mb-3" style="font-size: 3.5rem;"></i>
        <h4 class="fw-bold">Belum Ada Riwayat Pesanan</h4>
        <p class="text-muted mb-4">Anda belum pernah melakukan pemesanan ayam atau pakan di BangkokFarm.</p>
        <div>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-farm-primary px-4 py-2 rounded-pill fw-semibold">
                <i class="bi bi-grid-fill me-1"></i> Mulai Belanja Sekarang
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($daftar as $ps): ?>
            <?php
            // Ambil detail pesanan
            $stmt = $koneksi->prepare('SELECT d.*, p.nama_produk, p.gambar FROM detail_pesanan d
                                       JOIN produk p ON p.id_produk = d.id_produk WHERE d.id_pesanan = ?');
            $stmt->bind_param('i', $ps['id_pesanan']);
            $stmt->execute();
            $detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-light py-3 px-4 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="brand-icon-wrapper bg-success bg-opacity-10 text-success" style="width:36px;height:36px;font-size:1rem">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark fs-6">Pesanan #<?= (int) $ps['id_pesanan'] ?></span>
                                <span class="text-muted small ms-2">&middot; <?= date('d M Y, H:i', strtotime($ps['tanggal'])) ?> WIB</span>
                            </div>
                        </div>
                        <div>
                            <?= status_pesanan_badge($ps['status']) ?>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk Ayam / Item</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($detail as $d): ?>
                                    <tr>
                                        <td class="py-2">
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
                            </table>
                        </div>

                        <div class="row align-items-center pt-2 border-top">
                            <div class="col-md-7 small text-muted mb-2 mb-md-0">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Penerima: <strong><?= e($ps['nama_penerima']) ?></strong> (<?= e($ps['telepon']) ?>)<br>
                                Alamat: <?= e($ps['alamat_kirim']) ?>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="small text-muted">Total Pembayaran:</div>
                                <div class="harga fs-4 text-success mb-2"><?= rupiah($ps['total']) ?></div>
                                <a href="<?= link_whatsapp('Halo Admin BangkokFarm, saya ingin tanya status pengiriman pesanan #' . $ps['id_pesanan'] . ' a.n ' . $ps['nama_penerima']) ?>" 
                                   target="_blank" 
                                   class="btn btn-wa btn-sm px-3 rounded-pill">
                                    <i class="bi bi-whatsapp me-1"></i> Tanya Resi Kargo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

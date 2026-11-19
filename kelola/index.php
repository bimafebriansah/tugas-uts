<?php
require_once __DIR__ . '/../includes/fungsi.php';

wajib_role(['admin']);
$u      = user_login();
$id_user = (int) $u['id_user'];
$is_penjual = false;

// ---- Statistik dashboard ----
$stmt = $koneksi->prepare('SELECT COUNT(*) AS jml, COALESCE(SUM(stok),0) AS stok FROM produk WHERE 1');
$stmt->execute();
$stat_produk = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $koneksi->prepare('SELECT COUNT(DISTINCT ps.id_pesanan) AS jml, COALESCE(SUM(ps.total),0) AS omzet FROM pesanan ps WHERE 1');
$stmt->execute();
$stat_pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil 5 pesanan terbaru
$stmt = $koneksi->prepare('SELECT ps.*, u.nama AS nama_pembeli FROM pesanan ps 
                           JOIN users u ON u.id_user = ps.id_pembeli 
                           ORDER BY ps.tanggal DESC LIMIT 5');
$stmt->execute();
$pesanan_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$judul = 'Dashboard Admin Farm';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-speedometer2 text-success me-2"></i> Dashboard Admin Farm</h3>
        <p class="text-muted small mb-0">Kelola inventaris ayam laga, bibit DOC, status kargo, dan penjualan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/kelola/produk_form.php" class="btn btn-farm-primary px-3 rounded-pill fw-semibold">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Ayam/Produk
        </a>
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-outline-dark px-3 rounded-pill fw-semibold">
            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Toko
        </a>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card stat-green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.75rem">Total Produk Ayam</div>
                    <div class="fs-2 fw-bold text-dark mt-1"><?= (int) $stat_produk['jml'] ?></div>
                    <span class="small text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Terdaftar di Farm</span>
                </div>
                <div class="brand-icon-wrapper bg-success bg-opacity-10 text-success" style="width:44px;height:44px;font-size:1.3rem">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-gold">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.75rem">Stok Tersedia</div>
                    <div class="fs-2 fw-bold text-dark mt-1"><?= (int) $stat_produk['stok'] ?></div>
                    <span class="small text-warning-emphasis fw-semibold"><i class="bi bi-shield-fill"></i> Ekor / Unit Siap</span>
                </div>
                <div class="brand-icon-wrapper bg-warning bg-opacity-10 text-warning" style="width:44px;height:44px;font-size:1.3rem">
                    <i class="bi bi-egg-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.75rem">Total Pesanan</div>
                    <div class="fs-2 fw-bold text-dark mt-1"><?= (int) $stat_pesanan['jml'] ?></div>
                    <span class="small text-primary fw-semibold"><i class="bi bi-receipt"></i> Transaksi Masuk</span>
                </div>
                <div class="brand-icon-wrapper bg-primary bg-opacity-10 text-primary" style="width:44px;height:44px;font-size:1.3rem">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card stat-red">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.75rem">Total Omzet Penjualan</div>
                    <div class="fs-4 fw-bold text-dark mt-1 text-truncate"><?= rupiah($stat_pesanan['omzet']) ?></div>
                    <span class="small text-success fw-semibold"><i class="bi bi-graph-up-arrow"></i> Transaksi Berhasil</span>
                </div>
                <div class="brand-icon-wrapper bg-danger bg-opacity-10 text-danger" style="width:44px;height:44px;font-size:1.3rem">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Orders & Quick Actions -->
<div class="row g-4 mb-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-success me-2"></i> Pesanan Masuk Terbaru</h5>
                <a href="<?= BASE_URL ?>/kelola/pesanan.php" class="text-decoration-none text-success small fw-semibold">
                    Kelola Semua Pesanan <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">No. Pesanan</th>
                            <th>Pembeli</th>
                            <th>Waktu</th>
                            <th>Total Tagihan</th>
                            <th>Status Kargo</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($pesanan_terbaru)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pesanan transaksi masuk.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pesanan_terbaru as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= (int)$p['id_pesanan'] ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($p['nama_penerima']) ?></div>
                                    <div class="small text-muted"><?= e($p['telepon']) ?></div>
                                </td>
                                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
                                <td class="fw-bold text-success"><?= rupiah($p['total']) ?></td>
                                <td><?= status_pesanan_badge($p['status']) ?></td>
                                <td class="pe-4 text-end">
                                    <a href="<?= BASE_URL ?>/kelola/pesanan.php#pesanan-<?= (int)$p['id_pesanan'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-pencil-square"></i> Ubah
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & Farm Tips -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Menu Cepat Kelola</h5>
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/kelola/produk_form.php" class="btn btn-farm-primary py-2 text-start">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Ayam Bangkok / Produk
                </a>
                <a href="<?= BASE_URL ?>/kelola/produk.php" class="btn btn-outline-dark py-2 text-start">
                    <i class="bi bi-box-seam me-2"></i> Daftar &amp; Stok Produk
                </a>
                <a href="<?= BASE_URL ?>/kelola/pesanan.php" class="btn btn-outline-dark py-2 text-start">
                    <i class="bi bi-receipt me-2"></i> Kelola &amp; Update Status Resi
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-light p-4">
            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Standar Kargo Hewan Hidup:</h6>
            <ul class="small text-secondary ps-3 mb-0">
                <li>Pastikan ayam sudah diberi pakan jagung basah dan suplemen sebelum dimasukkan ke boks kargo.</li>
                <li>Tuliskan nomor telepon penerima secara jelas di sisi boks kayu.</li>
                <li>Fotokan resi kargo resmi ke pembeli begitu paket diserahkan ke agen stasiun/bus.</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

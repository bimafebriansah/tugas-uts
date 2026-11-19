<?php
require_once __DIR__ . '/includes/fungsi.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $koneksi->prepare('SELECT p.*, k.nama_kategori, u.nama AS nama_penjual, u.telepon AS telepon_penjual
                           FROM produk p
                           JOIN kategori k ON k.id_kategori = p.id_kategori
                           JOIN users u   ON u.id_user     = p.id_penjual
                           WHERE p.id_produk = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$p) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect(BASE_URL . '/index.php');
}

// ---- Produk terkait (kategori sama) ----
$stmt = $koneksi->prepare('SELECT p.*, k.nama_kategori, u.nama AS nama_penjual 
                           FROM produk p
                           JOIN kategori k ON k.id_kategori = p.id_kategori
                           JOIN users u ON u.id_user = p.id_penjual
                           WHERE p.id_kategori = ? AND p.id_produk <> ? ORDER BY RAND() LIMIT 4');
$stmt->bind_param('ii', $p['id_kategori'], $id);
$stmt->execute();
$terkait = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$judul = $p['nama_produk'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none text-muted"><i class="bi bi-house-door-fill"></i> Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?kategori=<?= (int) $p['id_kategori'] ?>" class="text-decoration-none text-muted"><?= e($p['nama_kategori']) ?></a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= e($p['nama_produk']) ?></li>
    </ol>
</nav>

<div class="row g-4 mb-5">
    <!-- Image & Guarantee Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
            <img src="<?= e(gambar_produk($p['gambar'])) ?>" class="gambar-detail" alt="<?= e($p['nama_produk']) ?>">
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-5"></i> Jaminan Standar BangkokFarm
            </h6>
            <div class="row g-2 small">
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3 h-100">
                        <i class="bi bi-box-seam text-warning me-1"></i> <strong>Kargo Resmi:</strong> Box kayu standar kereta/bus ekspres.
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3 h-100">
                        <i class="bi bi-patch-check text-success me-1"></i> <strong>Garansi Hidup:</strong> Penggantian bila mati di jalan.
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3 h-100">
                        <i class="bi bi-heart-pulse text-danger me-1"></i> <strong>Vaksin ND+IB:</strong> Bebas flu burung &amp; tetelo.
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3 h-100">
                        <i class="bi bi-chat-dots text-primary me-1"></i> <strong>Konsultasi:</strong> Gratis panduan rawatan harian.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details & Purchase Column -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-success-subtle text-success-emphasis px-3 py-2 rounded-pill fw-semibold">
                    <?= e($p['nama_kategori']) ?>
                </span>
                <?php if ((int)$p['stok'] > 0): ?>
                    <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-check2-circle"></i> Stok Tersedia: <?= (int)$p['stok'] ?> Ekor/Unit
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger-emphasis px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-x-circle"></i> Stok Habis
                    </span>
                <?php endif; ?>
            </div>

            <h2 class="fw-bold text-dark mb-2"><?= e($p['nama_produk']) ?></h2>

            <div class="d-flex align-items-center gap-2 text-muted small mb-3">
                <i class="bi bi-shop text-success fs-6"></i>
                <span>Peternak: <strong><?= e($p['nama_penjual']) ?></strong> &middot; Terverifikasi Kandang Resmi</span>
            </div>

            <div class="p-3 bg-light rounded-3 mb-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block">Harga Satuan Resmi:</span>
                    <span class="harga fs-2"><?= rupiah($p['harga']) ?></span>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill small">
                    <i class="bi bi-shield-lock-fill"></i> Transaksi Rekening Bersama Aman
                </span>
            </div>

            <!-- Action Buttons / Cart Form -->
            <?php if ((int) $p['stok'] > 0): ?>
                <form action="<?= BASE_URL ?>/aksi_keranjang.php" method="post" class="mb-4">
                    <input type="hidden" name="aksi" value="tambah">
                    <input type="hidden" name="id_produk" value="<?= (int) $p['id_produk'] ?>">
                    
                    <div class="row g-3 align-items-end">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold small text-muted">Jumlah Pesanan:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-calculator"></i></span>
                                <input type="number" name="qty" class="form-control" value="1" min="1" max="<?= (int) $p['stok'] ?>">
                            </div>
                        </div>
                        <div class="col-sm-8 d-grid">
                            <button class="btn btn-farm-primary py-2 fw-bold" type="submit">
                                <i class="bi bi-bag-plus-fill me-1"></i> Masukkan ke Keranjang Belanja
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger mb-4 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon maaf, stok untuk produk ayam ini sedang habis. Silakan hubungi kami untuk indent / pesan anakan berikutnya.
                </div>
            <?php endif; ?>

            <!-- WhatsApp Direct Consultation -->
            <div class="d-grid mb-4">
                <a href="<?= link_whatsapp('Halo BangkokFarm, saya ingin minta video fisik, video abar, dan info pengiriman untuk: ' . $p['nama_produk'] . ' (' . rupiah($p['harga']) . ')') ?>" 
                   target="_blank" 
                   class="btn btn-wa py-2 fw-semibold">
                    <i class="bi bi-whatsapp me-2 fs-5"></i> Minta Video Fisik / Abar via WhatsApp
                </a>
            </div>

            <!-- Detail Tabs -->
            <ul class="nav nav-pills mb-3 border-bottom pb-2" id="detailTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-2 px-3 fw-semibold" id="desc-tab" data-bs-toggle="pill" data-bs-target="#desc-pane" type="button" role="tab">
                        <i class="bi bi-file-text me-1"></i> Deskripsi &amp; Karakter
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2 px-3 fw-semibold" id="care-tab" data-bs-toggle="pill" data-bs-target="#care-pane" type="button" role="tab">
                        <i class="bi bi-heart me-1"></i> Panduan Pasca Tiba
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2 px-3 fw-semibold" id="warranty-tab" data-bs-toggle="pill" data-bs-target="#warranty-pane" type="button" role="tab">
                        <i class="bi bi-shield-check me-1"></i> Syarat Garansi
                    </button>
                </li>
            </ul>

            <div class="tab-content small" id="detailTabContent">
                <div class="tab-pane fade show active" id="desc-pane" role="tabpanel">
                    <div class="p-2 lh-lg text-secondary">
                        <?= nl2br(e($p['deskripsi'])) ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="care-pane" role="tabpanel">
                    <div class="p-2 lh-lg text-secondary">
                        <ol class="ps-3 mb-0">
                            <li><strong>Istirahatkan Ayam:</strong> Saat boks kargo tiba, buka perlahan di tempat teduh dan sejuk.</li>
                            <li><strong>Air Minum Gula Merah:</strong> Berikan air hangat dicampur sedikit gula merah atau larutan elektrolit untuk memulihkan stamina perjalanan.</li>
                            <li><strong>Jangan Langsung Dimandikan:</strong> Hindari memandikan atau menjemur ayam di hari pertama kedatangan agar tidak stres.</li>
                            <li><strong>Pakan Ringan:</strong> Berikan pakan basah atau pur sedikit demi sedikit, jangan langsung diberi pakan keras dalam porsi besar.</li>
                        </ol>
                    </div>
                </div>
                <div class="tab-pane fade" id="warranty-pane" role="tabpanel">
                    <div class="p-2 lh-lg text-secondary">
                        <ul class="ps-3 mb-0">
                            <li>Garansi 100% berlaku untuk kematian saat ayam dalam perjalanan kargo resmi (KIB / Kalog / Bus Rekanan).</li>
                            <li>Wajib merekam <strong>video unboxing</strong> tanpa jeda saat mengambil dan membuka boks paket di stasiun atau agen penerima.</li>
                            <li>Klaim dilakukan maksimal 2 jam setelah paket diambil dengan mengirimkan rekaman video ke WhatsApp customer service.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($terkait)): ?>
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Pilihan Ayam Terkait Lainnya</h4>
            <a href="<?= BASE_URL ?>/index.php?kategori=<?= (int)$p['id_kategori'] ?>" class="text-decoration-none text-success fw-semibold small">
                Lihat Semua Kategori Ini <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($terkait as $t): ?>
                <div class="col-6 col-md-3">
                    <div class="card card-produk h-100 shadow-sm">
                        <div class="card-produk-img-wrapper">
                            <span class="badge-kategori-chip"><?= e($t['nama_kategori']) ?></span>
                            <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $t['id_produk'] ?>">
                                <img src="<?= e(gambar_produk($t['gambar'])) ?>" alt="<?= e($t['nama_produk']) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title small">
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $t['id_produk'] ?>" class="text-decoration-none text-dark">
                                    <?= e($t['nama_produk']) ?>
                                </a>
                            </h6>
                            <div class="harga fs-6 mb-2"><?= rupiah($t['harga']) ?></div>
                            <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $t['id_produk'] ?>" class="btn btn-sm btn-outline-success w-100 mt-auto">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

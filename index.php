<?php
require_once __DIR__ . '/includes/fungsi.php';

// ---- Filter pencarian, kategori & pengurutan ----
$cari        = trim($_GET['cari'] ?? '');
$id_kategori = (int) ($_GET['kategori'] ?? 0);
$urutkan     = trim($_GET['urutkan'] ?? 'terbaru');

$sql = 'SELECT p.*, k.nama_kategori, u.nama AS nama_penjual
        FROM produk p
        JOIN kategori k ON k.id_kategori = p.id_kategori
        JOIN users u   ON u.id_user     = p.id_penjual
        WHERE 1=1';
$params = [];
$tipe   = '';

if ($cari !== '') {
    $sql .= ' AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)';
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
    $tipe .= 'ss';
}
if ($id_kategori > 0) {
    $sql .= ' AND p.id_kategori = ?';
    $params[] = $id_kategori;
    $tipe .= 'i';
}

// Urutan produk
switch ($urutkan) {
    case 'harga_asc':
        $sql .= ' ORDER BY p.harga ASC';
        break;
    case 'harga_desc':
        $sql .= ' ORDER BY p.harga DESC';
        break;
    case 'stok_desc':
        $sql .= ' ORDER BY p.stok DESC';
        break;
    case 'terbaru':
    default:
        $sql .= ' ORDER BY p.created_at DESC';
        break;
}

$stmt = $koneksi->prepare($sql);
if ($params) {
    $stmt->bind_param($tipe, ...$params);
}
$stmt->execute();
$daftar_produk = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ---- Daftar kategori untuk pill & dropdown ----
$kategori_list = $koneksi->query('SELECT * FROM kategori ORDER BY id_kategori ASC')->fetch_all(MYSQLI_ASSOC);

$judul = 'Sentra Ayam Bangkok & Ternak Unggul';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Section -->
<div class="hero-banner">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="hero-pill">
                <i class="bi bi-award-fill"></i> Peternakan Terverifikasi &middot; Garansi Tiba Hidup 100%
            </div>
            <h1 class="hero-title">
                Pusat Jual Beli <span class="highlight">Ayam Bangkok</span> &amp; Ternak Unggul Indonesia
            </h1>
            <p class="hero-lead">
                Sedia pejantan tangguh Bangkok F1, trah import Pakhoy, Birma, Shamo, bibit DOC berkualitas, babon cetak juara, serta jamu herbal pilihan dengan silsilah jelas.
            </p>

            <!-- Search Box in Hero -->
            <form class="hero-search-box" method="get">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="cari" class="form-control border-0 ps-0" placeholder="Cari jenis ayam, bibit, pakan..." value="<?= e($cari) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="kategori" class="form-select border-0 bg-light">
                            <option value="0">Semua Kategori Ternak</option>
                            <?php foreach ($kategori_list as $k): ?>
                                <option value="<?= (int) $k['id_kategori'] ?>" <?= $id_kategori === (int) $k['id_kategori'] ? 'selected' : '' ?>>
                                    <?= e($k['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn btn-farm-primary py-2" type="submit">
                            <i class="bi bi-search me-1"></i> Cari Ayam
                        </button>
                    </div>
                </div>
            </form>

            <div class="hero-trust-badges">
                <div class="trust-badge-item">
                    <i class="bi bi-check-circle-fill"></i> 100% Ayam Sehat &amp; Vaksin
                </div>
                <div class="trust-badge-item">
                    <i class="bi bi-box-seam-fill"></i> Kargo Kotak Kayu Khusus
                </div>
                <div class="trust-badge-item">
                    <i class="bi bi-camera-video-fill"></i> Video Abar &amp; Fisik Siap
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Pills Navigation -->
<div class="category-filter-wrapper">
    <div class="category-nav-pills">
        <a href="<?= BASE_URL ?>/index.php" class="pill-btn <?= $id_kategori === 0 ? 'active' : '' ?>">
            <i class="bi bi-grid-fill"></i> Semua Produk
        </a>
        <?php foreach ($kategori_list as $k): ?>
            <?php
            $icon = 'bi-tag-fill';
            if (stripos($k['nama_kategori'], 'Bangkok') !== false) $icon = 'bi-shield-shaded';
            elseif (stripos($k['nama_kategori'], 'Import') !== false) $icon = 'bi-airplane-fill';
            elseif (stripos($k['nama_kategori'], 'Kampung') !== false || stripos($k['nama_kategori'], 'Ternak') !== false) $icon = 'bi-egg-fill';
            elseif (stripos($k['nama_kategori'], 'DOC') !== false || stripos($k['nama_kategori'], 'Indukan') !== false) $icon = 'bi-heart-fill';
            elseif (stripos($k['nama_kategori'], 'Pakan') !== false || stripos($k['nama_kategori'], 'Jamu') !== false) $icon = 'bi-capsule';
            ?>
            <a href="<?= BASE_URL ?>/index.php?kategori=<?= (int)$k['id_kategori'] ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : '' ?>" 
               class="pill-btn <?= $id_kategori === (int)$k['id_kategori'] ? 'active' : '' ?>">
                <i class="bi <?= $icon ?>"></i> <?= e($k['nama_kategori']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Header Filter & Sorting Controls -->
<div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <?php if ($id_kategori > 0): ?>
                    <?php
                    $kat_nama = '';
                    foreach ($kategori_list as $k) {
                        if ((int)$k['id_kategori'] === $id_kategori) {
                            $kat_nama = $k['nama_kategori'];
                            break;
                        }
                    }
                    ?>
                    Kategori: <span class="text-success"><?= e($kat_nama) ?></span>
                <?php elseif ($cari !== ''): ?>
                    Hasil Pencarian: <span class="text-success">"<?= e($cari) ?>"</span>
                <?php else: ?>
                    Koleksi Ayam &amp; Hasil Ternak Pilihan
                <?php endif; ?>
            </h5>
            <small class="text-muted"><?= count($daftar_produk) ?> pilihan produk tersedia di kandang</small>
        </div>

        <form method="get" class="d-flex align-items-center gap-2">
            <?php if ($cari !== ''): ?><input type="hidden" name="cari" value="<?= e($cari) ?>"><?php endif; ?>
            <?php if ($id_kategori > 0): ?><input type="hidden" name="kategori" value="<?= (int)$id_kategori ?>"><?php endif; ?>
            <label class="small text-muted text-nowrap d-none d-sm-inline">Urutkan:</label>
            <select name="urutkan" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="terbaru" <?= $urutkan === 'terbaru' ? 'selected' : '' ?>>Terbaru Ditambahkan</option>
                <option value="harga_asc" <?= $urutkan === 'harga_asc' ? 'selected' : '' ?>>Harga: Terendah</option>
                <option value="harga_desc" <?= $urutkan === 'harga_desc' ? 'selected' : '' ?>>Harga: Tertinggi</option>
                <option value="stok_desc" <?= $urutkan === 'stok_desc' ? 'selected' : '' ?>>Stok Terbanyak</option>
            </select>
        </form>
    </div>
</div>

<!-- Product Grid -->
<div class="row g-4 mb-5">
    <?php if (empty($daftar_produk)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
                <i class="bi bi-search text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="fw-bold">Ayam atau Produk Tidak Ditemukan</h4>
                <p class="text-muted mb-3">Tidak ada produk yang sesuai dengan kriteria filter Anda saat ini.</p>
                <div>
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-farm-primary px-4 rounded-pill">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset Filter
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($daftar_produk as $p): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card card-produk h-100 shadow-sm">
                    <div class="card-produk-img-wrapper">
                        <span class="badge-kategori-chip">
                            <?= e($p['nama_kategori']) ?>
                        </span>
                        <?php if ((int)$p['stok'] > 0): ?>
                            <span class="badge-stok-chip">
                                <i class="bi bi-check2"></i> Stok: <?= (int)$p['stok'] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge-stok-chip bg-danger">
                                <i class="bi bi-x-circle"></i> Habis
                            </span>
                        <?php endif; ?>
                        
                        <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $p['id_produk'] ?>">
                            <img src="<?= e(gambar_produk($p['gambar'])) ?>" alt="<?= e($p['nama_produk']) ?>" loading="lazy">
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <div class="seller-info">
                            <i class="bi bi-patch-check-fill text-success"></i>
                            <span><?= e($p['nama_penjual']) ?> &middot; Farm Resmi</span>
                        </div>
                        
                        <h6 class="card-title">
                            <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $p['id_produk'] ?>" class="text-decoration-none text-dark">
                                <?= e($p['nama_produk']) ?>
                            </a>
                        </h6>
                        
                        <div class="mt-auto pt-2">
                            <div class="harga mb-3"><?= rupiah($p['harga']) ?></div>
                            
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $p['id_produk'] ?>" class="btn btn-farm-primary btn-sm py-2">
                                    <i class="bi bi-eye-fill me-1"></i> Lihat Spesifikasi
                                </a>
                                <a href="<?= link_whatsapp('Halo BangkokFarm, saya tertarik dengan ' . $p['nama_produk'] . ' (Rp ' . number_format($p['harga'], 0, ',', '.') . '). Bisakah kirim video abar dan detail kondisinya?') ?>" 
                                   target="_blank" 
                                   class="btn btn-wa btn-sm py-1">
                                    <i class="bi bi-whatsapp me-1"></i> Minta Video Ayam
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Why Choose Us / Keunggulan Farm -->
<div class="mb-5">
    <div class="text-center mb-4">
        <span class="badge bg-success-subtle text-success-emphasis px-3 py-2 rounded-pill fw-semibold mb-2">STANDAR PETERNAKAN MODERN</span>
        <h3 class="fw-bold text-dark">Kenapa Memilih BangkokFarm?</h3>
        <p class="text-muted">Komitmen kami memberikan ayam ternak berkualitas dengan standar pemeliharaan dan pengiriman terbaik.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="feature-card shadow-sm">
                <div class="feature-icon-wrapper bg-success-subtle text-success">
                    <i class="bi bi-shield-fill-check"></i>
                </div>
                <h5 class="fw-bold">Trah Juara &amp; Silsilah Jelas</h5>
                <p class="text-muted small mb-0">Semua pejantan dan indukan memiliki catatan genetik jelas, tulangan rapat, dan postur ideal standar kontes &amp; ternak.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card shadow-sm">
                <div class="feature-icon-wrapper bg-warning-subtle text-warning">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <h5 class="fw-bold">Vaksin &amp; Bebas Penyakit</h5>
                <p class="text-muted small mb-0">Ayam melewati karantina dan vaksinasi wajib (ND, IB, Gumboro) sehingga tiba di tangan Anda dalam kondisi bugar dan aktif.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card shadow-sm">
                <div class="feature-icon-wrapper bg-primary-subtle text-primary">
                    <i class="bi bi-box2-fill"></i>
                </div>
                <h5 class="fw-bold">Packing Kargo Berstandar</h5>
                <p class="text-muted small mb-0">Menggunakan boks kayu kuat dengan ventilasi luas, pakan jagung basah dan air minum untuk perjalanan 2 hari via kereta/bus.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card shadow-sm">
                <div class="feature-icon-wrapper bg-info-subtle text-info">
                    <i class="bi bi-chat-heart-fill"></i>
                </div>
                <h5 class="fw-bold">Garansi &amp; Bimbingan Rawatan</h5>
                <p class="text-muted small mb-0">Garansi ganti ayam jika mati di perjalanan kargo, plus gratis konsultasi resep pakan dan perawatan dari botoh berpengalaman.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

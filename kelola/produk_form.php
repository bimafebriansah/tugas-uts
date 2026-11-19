<?php
require_once __DIR__ . '/../includes/fungsi.php';

wajib_role(['admin']);
$u          = user_login();
$id_user    = (int) $u['id_user'];
$is_penjual = false;

$id     = (int) ($_GET['id'] ?? 0);
$edit   = $id > 0;
$produk = [
    'id_kategori' => '',
    'nama_produk' => '',
    'deskripsi'   => '',
    'harga'       => '',
    'stok'        => '',
    'gambar'      => '',
];

// ---- Ambil data produk saat mode edit ----
if ($edit) {
    $stmt = $koneksi->prepare('SELECT * FROM produk WHERE id_produk = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        set_flash('danger', 'Produk tidak ditemukan.');
        redirect(BASE_URL . '/kelola/produk.php');
    }
    $produk = $data;
}

$kategori_list = $koneksi->query('SELECT * FROM kategori ORDER BY id_kategori ASC')->fetch_all(MYSQLI_ASSOC);

// ---- Proses simpan ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = (int) ($_POST['id_kategori'] ?? 0);
    $nama        = trim($_POST['nama_produk'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $harga       = (float) ($_POST['harga'] ?? 0);
    $stok        = (int) ($_POST['stok'] ?? 0);
    $gambar_url  = trim($_POST['gambar_url'] ?? '');
    $gambar      = $produk['gambar']; // default: gambar lama

    if ($nama === '' || $id_kategori <= 0) {
        set_flash('danger', 'Nama ayam/produk dan kategori wajib diisi.');
        redirect(BASE_URL . '/kelola/produk_form.php' . ($edit ? '?id=' . $id : ''));
    }

    // ---- Unggah gambar (opsional) ----
    if (!empty($_FILES['gambar']['name'])) {
        $dir = __DIR__ . '/../assets/img/produk/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $izin = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext  = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $izin, true)) {
            set_flash('danger', 'Format gambar harus JPG, PNG, WEBP, atau GIF.');
            redirect(BASE_URL . '/kelola/produk_form.php' . ($edit ? '?id=' . $id : ''));
        }
        $nama_file = 'produk_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $dir . $nama_file)) {
            $gambar = $nama_file;
        } else {
            set_flash('danger', 'Gagal mengunggah gambar produk.');
            redirect(BASE_URL . '/kelola/produk_form.php' . ($edit ? '?id=' . $id : ''));
        }
    } elseif ($gambar_url !== '') {
        $gambar = $gambar_url;
    }

    if ($edit) {
        $stmt = $koneksi->prepare('UPDATE produk SET id_kategori=?, nama_produk=?, deskripsi=?, harga=?, stok=?, gambar=?
                                   WHERE id_produk=?');
        $stmt->bind_param('issdisi', $id_kategori, $nama, $deskripsi, $harga, $stok, $gambar, $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Data produk ayam berhasil diperbarui.');
    } else {
        $stmt = $koneksi->prepare('INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iissdis', $id_user, $id_kategori, $nama, $deskripsi, $harga, $stok, $gambar);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Produk ayam baru berhasil ditambahkan ke katalog farm.');
    }
    redirect(BASE_URL . '/kelola/produk.php');
}

$judul = $edit ? 'Edit Data Produk Ayam' : 'Tambah Ayam / Produk Baru';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="bi bi-<?= $edit ? 'pencil-square text-primary' : 'plus-circle-fill text-success' ?> me-2"></i> <?= $judul ?>
        </h3>
        <p class="text-muted small mb-0">Isi data identitas, spesifikasi usia, bobot, trah, harga, dan foto ayam ternak.</p>
    </div>
    <a href="<?= BASE_URL ?>/kelola/produk.php" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Nama Produk / Jenis Ayam</label>
                    <input type="text" name="nama_produk" class="form-control" value="<?= e($produk['nama_produk']) ?>" required placeholder="Contoh: Ayam Bangkok Super Wiring Kuning (Pejantan F1)">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_list as $k): ?>
                                <option value="<?= (int) $k['id_kategori'] ?>" <?= (int) $produk['id_kategori'] === (int) $k['id_kategori'] ? 'selected' : '' ?>>
                                    <?= e($k['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-dark">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" value="<?= e($produk['harga']) ?>" min="0" step="500" required placeholder="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-dark">Stok (Ekor/Unit)</label>
                        <input type="number" name="stok" class="form-control" value="<?= e($produk['stok']) ?>" min="0" required placeholder="1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Deskripsi &amp; Spesifikasi Lengkap</label>
                    <textarea name="deskripsi" class="form-control" rows="5" placeholder="Tuliskan spesifikasi usia, berat/bobot, tulangan, karakter pukulan/gaya bertarung, riwayat vaksin, dll..."><?= e($produk['deskripsi']) ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">Unggah Foto (JPG/PNG/WEBP)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewFile(this)">
                        <div class="form-text">Biarkan kosong bila tidak ingin mengganti foto saat ini.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">Atau URL Foto Eksternal</label>
                        <input type="text" name="gambar_url" class="form-control" placeholder="https://..." value="<?= preg_match('#^https?://#i', $produk['gambar']) ? e($produk['gambar']) : '' ?>" oninput="previewUrl(this.value)">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-farm-primary px-4 py-2 fw-semibold rounded-3" type="submit">
                        <i class="bi bi-save me-1"></i> Simpan Data Produk
                    </button>
                    <a href="<?= BASE_URL ?>/kelola/produk.php" class="btn btn-outline-dark px-3 py-2 rounded-3">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Pratinjau Foto Produk</h6>
            <div class="text-center">
                <img id="imgPreview" src="<?= e(gambar_produk($produk['gambar'])) ?>" class="img-fluid rounded-4 shadow-sm" style="max-height: 280px; width: 100%; object-fit: cover;" alt="Pratinjau">
            </div>
            <div class="alert alert-info mt-3 mb-0 small">
                <i class="bi bi-camera-fill me-1"></i> Gunakan foto ayam yang jelas di bawah cahaya terang agar menarik minat peternak.
            </div>
        </div>
    </div>
</div>

<script>
function previewFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
function previewUrl(url) {
    if (url.trim() !== '') {
        document.getElementById('imgPreview').src = url;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

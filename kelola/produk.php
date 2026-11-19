<?php
require_once __DIR__ . '/../includes/fungsi.php';

wajib_role(['admin']);
$u          = user_login();
$id_user    = (int) $u['id_user'];
$is_penjual = false;

$stmt = $koneksi->prepare('SELECT p.*, k.nama_kategori, u.nama AS nama_penjual FROM produk p
                           JOIN kategori k ON k.id_kategori = p.id_kategori
                           JOIN users u ON u.id_user = p.id_penjual ORDER BY p.id_produk DESC');
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$judul = 'Kelola Produk & Inventaris Ayam';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-box-seam text-success me-2"></i> Inventaris Ayam &amp; Produk Farm</h3>
        <p class="text-muted small mb-0">Total <?= count($daftar) ?> produk ayam bangkok, bibit ternak, dan pakan terdaftar di sistem.</p>
    </div>
    <a href="<?= BASE_URL ?>/kelola/produk_form.php" class="btn btn-farm-primary px-3 rounded-pill fw-semibold">
        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Ayam/Produk Baru
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="cariTabel" class="form-control form-control-sm bg-white" placeholder="Cari nama ayam / kategori..." style="max-width: 280px;" onkeyup="filterTable()">
        </div>
        <span class="badge bg-secondary rounded-pill small"><?= count($daftar) ?> Total Item</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelProduk">
            <thead class="table-light small">
                <tr>
                    <th class="ps-4" style="width: 50px;">#</th>
                    <th style="width: 70px;">Foto</th>
                    <th>Nama Ayam / Produk</th>
                    <th>Kategori</th>
                    <th>Harga Satuan</th>
                    <th class="text-center">Stok</th>
                    <th class="pe-4 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($daftar)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada produk terdaftar.</td></tr>
            <?php else: ?>
                <?php foreach ($daftar as $i => $p): ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                        <td>
                            <img src="<?= e(gambar_produk($p['gambar'])) ?>" width="52" height="52" style="object-fit:cover" class="rounded-3 shadow-sm" alt="<?= e($p['nama_produk']) ?>">
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $p['id_produk'] ?>" target="_blank" class="fw-bold text-dark text-decoration-none d-block">
                                <?= e($p['nama_produk']) ?>
                            </a>
                            <span class="text-muted small" style="font-size:0.75rem"><?= e($p['nama_penjual']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-1">
                                <?= e($p['nama_kategori']) ?>
                            </span>
                        </td>
                        <td class="fw-bold text-dark">
                            <?= rupiah($p['harga']) ?>
                        </td>
                        <td class="text-center">
                            <?php if ((int)$p['stok'] <= 0): ?>
                                <span class="badge bg-danger rounded-pill">Habis</span>
                            <?php elseif ((int)$p['stok'] <= 3): ?>
                                <span class="badge bg-warning text-dark rounded-pill"><?= (int)$p['stok'] ?> ekor (Menipis)</span>
                            <?php else: ?>
                                <span class="badge bg-success bg-opacity-75 rounded-pill"><?= (int)$p['stok'] ?> ekor</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end text-nowrap">
                            <a href="<?= BASE_URL ?>/detail.php?id=<?= (int) $p['id_produk'] ?>" class="btn btn-sm btn-outline-secondary rounded-3" target="_blank" title="Lihat di Toko">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/kelola/produk_form.php?id=<?= (int) $p['id_produk'] ?>" class="btn btn-sm btn-outline-primary rounded-3" title="Edit Produk">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/kelola/produk_hapus.php?id=<?= (int) $p['id_produk'] ?>" class="btn btn-sm btn-outline-danger rounded-3"
                               onclick="return confirm('Yakin ingin menghapus produk <?= e(addslashes($p['nama_produk'])) ?>?')" title="Hapus">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    var input = document.getElementById("cariTabel");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("tabelProduk");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var tdNama = tr[i].getElementsByTagName("td")[2];
        var tdKat = tr[i].getElementsByTagName("td")[3];
        if (tdNama || tdKat) {
            var txtNama = tdNama ? (tdNama.textContent || tdNama.innerText) : "";
            var txtKat = tdKat ? (tdKat.textContent || tdKat.innerText) : "";
            if (txtNama.toLowerCase().indexOf(filter) > -1 || txtKat.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

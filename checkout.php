<?php
require_once __DIR__ . '/includes/fungsi.php';

wajib_login();
$id_user = (int) $_SESSION['user']['id_user'];

/** Ambil isi keranjang beserta data produk terbaru */
function ambil_keranjang(mysqli $koneksi, int $id_user): array
{
    $stmt = $koneksi->prepare('SELECT k.id_keranjang, k.qty, p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar, u.nama AS nama_penjual
                               FROM keranjang k 
                               JOIN produk p ON p.id_produk = k.id_produk
                               JOIN users u ON u.id_user = p.id_penjual
                               WHERE k.id_user = ?');
    $stmt->bind_param('i', $id_user);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

$items = ambil_keranjang($koneksi, $id_user);

if (empty($items)) {
    set_flash('warning', 'Keranjang belanja Anda masih kosong.');
    redirect(BASE_URL . '/keranjang.php');
}

$u = user_login();

// ---- Proses simpan transaksi ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penerima = trim($_POST['nama_penerima'] ?? '');
    $telepon       = trim($_POST['telepon'] ?? '');
    $alamat        = trim($_POST['alamat'] ?? '');

    if ($nama_penerima === '' || $alamat === '') {
        set_flash('danger', 'Nama penerima dan alamat pengiriman wajib diisi lengkap.');
        redirect(BASE_URL . '/checkout.php');
    }

    $koneksi->begin_transaction();
    try {
        // 1) Hitung total & validasi stok terbaru
        $total = 0;
        foreach ($items as $it) {
            if ($it['qty'] > (int) $it['stok']) {
                throw new Exception('Stok produk "' . $it['nama_produk'] . '" tidak mencukupi (tersisa ' . $it['stok'] . ').');
            }
            $total += $it['harga'] * $it['qty'];
        }

        // 2) Simpan header pesanan
        $status = 'pending';
        $stmt = $koneksi->prepare('INSERT INTO pesanan (id_pembeli, total, status, nama_penerima, telepon, alamat_kirim)
                                   VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('idssss', $id_user, $total, $status, $nama_penerima, $telepon, $alamat);
        $stmt->execute();
        $id_pesanan = $stmt->insert_id;
        $stmt->close();

        // 3) Simpan detail pesanan + kurangi stok
        $stmt_detail = $koneksi->prepare('INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, harga_satuan, subtotal)
                                          VALUES (?, ?, ?, ?, ?)');
        $stmt_stok = $koneksi->prepare('UPDATE produk SET stok = stok - ? WHERE id_produk = ?');

        foreach ($items as $it) {
            $subtotal     = $it['harga'] * $it['qty'];
            $harga        = $it['harga'];
            $qty          = (int) $it['qty'];
            $id_produk    = (int) $it['id_produk'];

            $stmt_detail->bind_param('iiddd', $id_pesanan, $id_produk, $qty, $harga, $subtotal);
            $stmt_detail->execute();

            $stmt_stok->bind_param('ii', $qty, $id_produk);
            $stmt_stok->execute();
        }
        $stmt_detail->close();
        $stmt_stok->close();

        // 4) Kosongkan keranjang
        $stmt = $koneksi->prepare('DELETE FROM keranjang WHERE id_user = ?');
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
        $stmt->close();

        $koneksi->commit();
        set_flash('success', 'Pesanan pesanan ayam berhasil dibuat! Silakan lakukan pembayaran dan konfirmasi.');
        redirect(BASE_URL . '/pesanan_sukses.php?id=' . $id_pesanan);
    } catch (Throwable $ex) {
        $koneksi->rollback();
        set_flash('danger', 'Gagal memproses pesanan: ' . $ex->getMessage());
        redirect(BASE_URL . '/checkout.php');
    }
}

$total = 0;
foreach ($items as $it) {
    $total += $it['harga'] * $it['qty'];
}

$judul = 'Data Pengiriman & Checkout';
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
                    <span class="badge bg-success rounded-circle p-2 px-3 fw-bold mb-1">2</span>
                    <div class="small fw-bold text-success">Data Pengiriman Kargo</div>
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

<div class="row g-4 mb-5">
    <!-- Form Pengiriman -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h4 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-success"></i> Formulir Alamat &amp; Data Penerima
            </h4>
            <p class="text-muted small mb-4">
                Pastikan nomor telepon WhatsApp aktif agar pihak kargo (KIB / Kalog / Bus Rekanan) dapat menghubungi Anda saat paket ayam tiba di stasiun atau alamat.
            </p>

            <form method="post" id="formCheckout">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Nama Lengkap Penerima</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" name="nama_penerima" class="form-control border-start-0" value="<?= e($u['nama']) ?>" required placeholder="Masukkan nama penerima">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">No. Telepon / WhatsApp Aktif</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-muted"></i></span>
                        <input type="text" name="telepon" class="form-control border-start-0" value="<?= e($u['telepon'] ?? '') ?>" required placeholder="Contoh: 08123456789">
                    </div>
                    <div class="form-text">Pihak ekspedisi kargo hewan akan mengontak nomor ini saat ayam siap diambil.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small text-dark">Alamat Lengkap &amp; Catatan Kargo (Stasiun/Terminal Terdekat)</label>
                    <textarea name="alamat" class="form-control" rows="4" required placeholder="Contoh: Jl. Diponegoro No. 12, RT 02/05, Kec. Coblong, Kota Bandung. (Ambil di Agen KIB Stasiun Bandung Kota)"><?= e($u['alamat'] ?? '') ?></textarea>
                    <div class="form-text">Untuk luar kota, sebutkan stasiun kereta KIB/Kalog atau terminal bus terdekat Anda.</div>
                </div>

                <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25 mb-4 small text-secondary">
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-warning me-1"></i> Prosedur Kargo Hewan Hidup:</h6>
                    <ul class="ps-3 mb-0">
                        <li>Ayam dikirim dalam boks kayu standar karantina dan diberi bekal makanan/minum.</li>
                        <li>Ongkos kirim kargo hewan resmi dibayarkan saat konfirmasi resi atau sistem bayar di tujuan (COD ongkir) sesuai kesepakatan agen.</li>
                    </ul>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-farm-primary py-3 fw-bold rounded-3">
                        <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi &amp; Buat Pesanan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Review Barang -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Rincian Ayam / Produk Belanja</h5>

            <div class="mb-3" style="max-height: 280px; overflow-y: auto;">
                <?php foreach ($items as $it): ?>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= e(gambar_produk($it['gambar'])) ?>" width="48" height="48" style="object-fit:cover" class="rounded-3 shadow-sm" alt="<?= e($it['nama_produk']) ?>">
                            <div>
                                <div class="small fw-bold text-dark"><?= e($it['nama_produk']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem"><?= (int)$it['qty'] ?> ekor x <?= rupiah($it['harga']) ?></div>
                            </div>
                        </div>
                        <div class="small fw-bold text-success text-nowrap">
                            <?= rupiah($it['harga'] * $it['qty']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-between mb-2 small text-secondary">
                <span>Subtotal Produk:</span>
                <span class="fw-bold text-dark"><?= rupiah($total) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2 small text-secondary">
                <span>Box Kayu Ventilasi:</span>
                <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Sudah Termasuk</span>
            </div>
            <div class="d-flex justify-content-between mb-3 small text-secondary">
                <span>Garansi Tiba Hidup:</span>
                <span class="text-success fw-semibold"><i class="bi bi-shield-check me-1"></i> 100% Terlindungi</span>
            </div>

            <div class="border-top pt-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="small text-muted d-block">Total Tagihan:</span>
                    <span class="harga fs-3 text-success"><?= rupiah($total) ?></span>
                </div>
            </div>

            <div class="small text-muted text-center">
                <i class="bi bi-shield-lock-fill text-success me-1"></i> Data dan identitas peternak dijamin aman dan terverifikasi.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

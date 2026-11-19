<?php
require_once __DIR__ . '/includes/fungsi.php';

if (sudah_login()) {
    redirect(BASE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';
    $role     = 'user';
    $telepon  = trim($_POST['telepon'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');

    $error = '';
    if ($nama === '' || $email === '' || $password === '') {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirm) {
        $error = 'Konfirmasi password tidak cocok.';
    }

    if ($error === '') {
        // Cek email unik
        $stmt = $koneksi->prepare('SELECT id_user FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $ada = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($ada) {
            $error = 'Email sudah terdaftar, gunakan email lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare('INSERT INTO users (nama, email, password, role, telepon, alamat) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $nama, $email, $hash, $role, $telepon, $alamat);
            if ($stmt->execute()) {
                $stmt->close();
                set_flash('success', 'Pendaftaran akun berhasil! Silakan login.');
                redirect(BASE_URL . '/login.php');
            }
            $error = 'Registrasi gagal: ' . $koneksi->error;
            $stmt->close();
        }
    }
    set_flash('danger', $error);
    redirect(BASE_URL . '/register.php');
}

$judul = 'Daftar Akun Baru';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-8 col-lg-7">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            <div class="p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-icon-wrapper mx-auto mb-3" style="width:52px;height:52px;font-size:1.6rem">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Daftar Akun <?= APP_NAME ?></h3>
                    <p class="text-muted small">Bergabung dengan komunitas peternak &amp; pecinta ayam unggulan se-Indonesia</p>
                </div>

                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="nama" class="form-control border-start-0" required placeholder="Nama lengkap Anda">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0" required placeholder="contoh@gmail.com">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0" required minlength="6" placeholder="Minimal 6 karakter">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                <input type="password" name="konfirmasi" class="form-control border-start-0" required placeholder="Ulangi password">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">No. Telepon / WhatsApp Aktif</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-muted"></i></span>
                                <input type="text" name="telepon" class="form-control border-start-0" required placeholder="08123456789">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Alamat Lengkap (Domisili / Kota Penerima Kargo)</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor, kecamatan, kota/kabupaten, dan provinsi"></textarea>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button class="btn btn-farm-primary py-3 fw-bold rounded-3" type="submit">
                            <i class="bi bi-person-check-fill me-1"></i> Selesaikan Pendaftaran
                        </button>
                    </div>
                </form>

                <div class="text-center small text-muted mt-4">
                    Sudah punya akun peternak? <a href="<?= BASE_URL ?>/login.php" class="text-success fw-bold text-decoration-none">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

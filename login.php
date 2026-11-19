<?php
require_once __DIR__ . '/includes/fungsi.php';

if (sudah_login()) {
    redirect(BASE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        set_flash('danger', 'Email dan password wajib diisi.');
    } else {
        $stmt = $koneksi->prepare('SELECT id_user, nama, email, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id_user' => $user['id_user'],
                'nama'    => $user['nama'],
                'email'   => $user['email'],
                'role'    => $user['role'],
            ];
            set_flash('success', 'Selamat datang kembali, ' . $user['nama'] . '!');
            if ($user['role'] === 'admin') {
                redirect(BASE_URL . '/kelola/index.php');
            }
            redirect(BASE_URL . '/index.php');
        }
        set_flash('danger', 'Email atau password yang Anda masukkan tidak sesuai.');
    }
    redirect(BASE_URL . '/login.php');
}

$judul = 'Masuk Akun';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            <div class="p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-icon-wrapper mx-auto mb-3" style="width:52px;height:52px;font-size:1.6rem">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Masuk ke <?= APP_NAME ?></h3>
                    <p class="text-muted small">Sentra Ayam Bangkok &amp; Ternak Unggul Indonesia</p>
                </div>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="inputEmail" class="form-control border-start-0" required autofocus placeholder="contoh@ayammart.test">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-dark">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" id="inputPassword" class="form-control border-start-0" required placeholder="Masukkan password">
                        </div>
                    </div>

                    <button class="btn btn-farm-primary w-100 py-3 fw-bold rounded-3 mb-3" type="submit">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </form>

                <div class="text-center small text-muted">
                    Belum memiliki akun peternak? <a href="<?= BASE_URL ?>/register.php" class="text-success fw-bold text-decoration-none">Daftar di sini</a>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="small fw-bold text-muted mb-2 text-center">Akun Demo Pengujian:</div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="document.getElementById('inputEmail').value='admin@ayammart.test'; document.getElementById('inputPassword').value='admin123';">
                            <i class="bi bi-person-gear"></i> Admin Farm
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="document.getElementById('inputEmail').value='user@ayammart.test'; document.getElementById('inputPassword').value='user123';">
                            <i class="bi bi-person"></i> Pembeli
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

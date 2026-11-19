<?php
require_once __DIR__ . '/fungsi.php';
$judul = $judul ?? APP_NAME;
$u = user_login();
$jml_keranjang = jumlah_keranjang($koneksi);
$curr_page = basename($_SERVER['PHP_SELF'] ?? '');
$tagline = defined('APP_TAGLINE') ? APP_TAGLINE : 'Sentra Ayam Bangkok & Ternak Unggulan';
$wa_display = defined('APP_WA_DISPLAY') ? APP_WA_DISPLAY : '0812-8899-7766';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul) ?> &mdash; <?= APP_NAME ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap & Icons -->
    <link href="<?= BASE_URL ?>/assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom Stylesheet with Cache Buster -->
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>" rel="stylesheet">

    <!-- Critical Inlined Styles for Guaranteed 100% Styling on Any Browser/Cache -->
    <style>
        :root {
            --farm-emerald: #166534;
            --farm-emerald-dark: #14532d;
            --farm-gold: #f59e0b;
            --slate-900: #0f172a;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .top-announcement {
            background: linear-gradient(90deg, #14532d 0%, #166534 60%, #b45309 100%) !important;
            color: #fef3c7 !important;
            font-size: 0.8125rem;
            padding: 0.45rem 0;
            font-weight: 500;
        }
        .top-announcement a {
            color: #ffffff !important;
            text-decoration: underline;
        }
        .navbar-custom {
            background-color: #0f172a !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.75rem 0;
        }
        .navbar-custom .navbar-brand {
            color: #ffffff !important;
            font-size: 1.35rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .brand-icon-wrapper {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.35);
        }
        .brand-tagline {
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.65);
            text-transform: uppercase;
            display: block;
            margin-top: -3px;
            font-weight: 600;
        }
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 0.5rem 0.85rem !important;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-size: 0.925rem;
        }
        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
        }
        .hero-banner {
            position: relative;
            border-radius: 1.25rem;
            overflow: hidden;
            background-color: #0f172a;
            background-image: linear-gradient(120deg, rgba(15, 23, 42, 0.93) 0%, rgba(20, 83, 45, 0.88) 55%, rgba(180, 83, 9, 0.65) 100%), url('<?= BASE_URL ?>/assets/img/hero_banner.jpg');
            background-size: cover;
            background-position: center center;
            color: #ffffff;
            padding: 3rem 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
            margin-bottom: 2rem;
        }
        @media (min-width: 768px) {
            .hero-banner { padding: 4.5rem 3.5rem; }
        }
        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            padding: 0.4rem 1rem;
            border-radius: 50rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #fef08a;
            margin-bottom: 1.25rem;
        }
        .hero-title {
            font-size: calc(1.8rem + 1.2vw);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1rem;
            color: #ffffff;
        }
        .hero-title .highlight {
            color: #fde047;
            background: linear-gradient(120deg, #fde047 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-lead {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 620px;
            margin-bottom: 1.75rem;
        }
        .hero-search-box {
            background: #ffffff;
            border-radius: 1rem;
            padding: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            max-width: 720px;
        }
        .category-filter-wrapper {
            overflow-x: auto;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .category-nav-pills {
            display: flex;
            gap: 0.6rem;
            flex-wrap: nowrap;
            white-space: nowrap;
        }
        .category-nav-pills .pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.15rem;
            border-radius: 50rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155 !important;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            text-decoration: none !important;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .category-nav-pills .pill-btn:hover {
            color: #166534 !important;
            border-color: #166534;
            background: #f0fdf4;
            transform: translateY(-2px);
        }
        .category-nav-pills .pill-btn.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #166534 0%, #14532d 100%);
            border-color: #166534;
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.35);
        }
        .card-produk {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
        }
        .card-produk:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.12) !important;
            border-color: #cbd5e1;
        }
        .card-produk-img-wrapper {
            position: relative;
            width: 100%;
            padding-top: 100%;
            background: #f1f5f9;
            overflow: hidden;
        }
        .card-produk-img-wrapper img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .card-produk:hover .card-produk-img-wrapper img {
            transform: scale(1.06);
        }
        .badge-kategori-chip {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.35rem 0.65rem;
            border-radius: 50rem;
            background: rgba(15, 23, 42, 0.8);
            color: #ffffff;
            backdrop-filter: blur(6px);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            z-index: 2;
        }
        .badge-stok-chip {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.35rem 0.65rem;
            border-radius: 50rem;
            background: rgba(22, 101, 52, 0.9);
            color: #ffffff;
            backdrop-filter: blur(6px);
            z-index: 2;
        }
        .harga {
            color: #14532d;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
        }
        .btn-farm-primary {
            background: linear-gradient(135deg, #166534 0%, #14532d 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-farm-primary:hover {
            background: linear-gradient(135deg, #15803d 0%, #166534 100%);
            color: #ffffff;
        }
        .btn-farm-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 0.5rem;
        }
        .btn-wa {
            background: #25d366;
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
        }
        .btn-wa:hover {
            background: #1eb956;
            color: #ffffff;
        }
        .btn-floating-wa {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 58px;
            height: 58px;
            background: #25d366;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.45);
            z-index: 999;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-floating-wa:hover {
            background: #1eb956;
            color: #ffffff;
            transform: scale(1.08);
        }
    </style>
</head>
<body>

<!-- Announcement Bar -->
<div class="top-announcement text-center">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <span class="d-none d-md-inline">
            <i class="bi bi-box-seam me-1"></i> Pengiriman Kargo Ayam Hidup Seluruh Indonesia &middot; Garansi Tiba Hidup 100%
        </span>
        <span class="mx-auto mx-md-0">
            <i class="bi bi-whatsapp me-1"></i> Konsultasi &amp; Video Ayam: <a href="<?= link_whatsapp('Halo BangkokFarm, saya ingin tanya ketersediaan stok ayam...') ?>" target="_blank" class="fw-bold"><?= e($wa_display) ?></a>
        </span>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
            <div class="brand-icon-wrapper">
                <i class="bi bi-shield-fill-check"></i>
            </div>
            <div>
                <span class="brand-font fs-4 text-white"><?= APP_NAME ?></span>
                <span class="brand-tagline"><?= e($tagline) ?></span>
            </div>
        </a>

        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navUtama" aria-controls="navUtama" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
        </button>

        <div class="collapse navbar-collapse" id="navUtama">
            <ul class="navbar-nav me-auto ps-lg-4">
                <li class="nav-item">
                    <a class="nav-link <?= $curr_page === 'index.php' && empty($_GET['kategori']) ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="bi bi-grid-fill me-1"></i> Katalog Semua
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['kategori']) && (int)$_GET['kategori'] === 1 ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?kategori=1">
                        Ayam Bangkok
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['kategori']) && (int)$_GET['kategori'] === 2 ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?kategori=2">
                        Trah Import
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['kategori']) && (int)$_GET['kategori'] === 3 ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?kategori=3">
                        Ayam Ternak &amp; DOC
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['kategori']) && (int)$_GET['kategori'] === 5 ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?kategori=5">
                        Pakan &amp; Jamu
                    </a>
                </li>
                <?php if ($u && $u['role'] === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-warning" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear-fill me-1"></i> Admin Panel
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/kelola/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/kelola/produk.php"><i class="bi bi-box-seam me-2"></i> Kelola Produk</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/kelola/produk_form.php"><i class="bi bi-plus-circle me-2"></i> Tambah Ayam/Produk</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/kelola/pesanan.php"><i class="bi bi-receipt me-2"></i> Kelola Pesanan</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($u && $u['role'] === 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $curr_page === 'riwayat.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/riwayat.php">
                            <i class="bi bi-receipt-cutoff me-1"></i> Pesanan Saya
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav align-items-lg-center gap-2">
                <li class="nav-item">
                    <a class="nav-link position-relative py-2 px-3 border border-secondary rounded-pill" href="<?= BASE_URL ?>/keranjang.php">
                        <i class="bi bi-bag-check-fill text-warning me-1"></i>
                        <span>Keranjang</span>
                        <?php if ($jml_keranjang > 0): ?>
                            <span class="badge rounded-pill bg-danger cart-badge" style="position:absolute;top:-4px;right:-4px;font-size:0.7rem"><?= (int) $jml_keranjang ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($u): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1 px-3 bg-secondary bg-opacity-25 rounded-pill" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-warning"></i>
                            <span class="small fw-semibold"><?= e($u['nama']) ?></span>
                            <span class="badge bg-warning text-dark text-uppercase" style="font-size:0.65rem"><?= e($u['role']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow">
                            <li class="px-3 py-2 small text-muted border-bottom border-secondary">
                                Masuk sebagai <strong><?= e($u['email']) ?></strong>
                            </li>
                            <?php if ($u['role'] === 'user'): ?>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/riwayat.php"><i class="bi bi-clock-history me-2"></i> Riwayat Pesanan</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/kelola/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard Admin</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/kelola/pesanan.php"><i class="bi bi-receipt me-2"></i> Pesanan Masuk</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-farm-amber btn-sm px-3 rounded-pill" href="<?= BASE_URL ?>/register.php">
                            <i class="bi bi-person-plus-fill me-1"></i> Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php tampilkan_flash(); ?>

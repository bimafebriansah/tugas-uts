    </div>
</main>

<!-- Floating WhatsApp Button -->
<a href="<?= link_whatsapp('Halo BangkokFarm, saya ingin konsultasi pilihan ayam bangkok & informasi pengiriman...') ?>" 
   class="btn-floating-wa" 
   target="_blank" 
   rel="noopener noreferrer"
   title="Konsultasi & Minta Video Ayam via WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<!-- Modern Farm Footer -->
<footer class="footer-farm">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon-wrapper" style="width:34px;height:34px;font-size:1.1rem">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <span class="brand-font fs-4 text-white"><?= APP_NAME ?></span>
                </div>
                <p class="small mb-3">
                    Sentra peternakan dan penjualan Ayam Bangkok kualitas super, ayam laga trah impor (Pakhoy, Birma, Shamo), ayam kampung unggul, DOC berkualitas, serta suplemen dan jamu herbal terpercaya.
                </p>
                <div class="small text-white-50">
                    <p class="mb-1"><i class="bi bi-geo-alt-fill text-warning me-2"></i> Kandang Pusat: Jl. Raya Peternakan No. 88, Bogor - Jawa Barat</p>
                    <p class="mb-1"><i class="bi bi-whatsapp text-success me-2"></i> CS Hotline: <?= defined('APP_WA_DISPLAY') ? APP_WA_DISPLAY : '0812-8899-7766' ?></p>
                    <p class="mb-0"><i class="bi bi-clock-fill text-warning me-2"></i> Buka Setiap Hari: 07.00 - 18.00 WIB</p>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="fs-6">Kategori Unggulan</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= BASE_URL ?>/index.php?kategori=1"><i class="bi bi-chevron-right me-1 small"></i> Ayam Bangkok Super</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?kategori=2"><i class="bi bi-chevron-right me-1 small"></i> Trah Pakhoy &amp; Birma</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?kategori=2"><i class="bi bi-chevron-right me-1 small"></i> Ayam Shamo Jepang</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?kategori=3"><i class="bi bi-chevron-right me-1 small"></i> Bibit DOC &amp; Joper</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?kategori=4"><i class="bi bi-chevron-right me-1 small"></i> Babon Cetak Juara</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?kategori=5"><i class="bi bi-chevron-right me-1 small"></i> Jamu &amp; Pakan Pur</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="fs-6">Standar &amp; Garansi</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-patch-check-fill text-success mt-1"></i>
                        <span><strong>Garansi Tiba Hidup:</strong> Penggantian 100% jika terjadi kematian dalam perjalanan kargo.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-box2-heart-fill text-warning mt-1"></i>
                        <span><strong>Packing Kargo Khusus:</strong> Kotak kayu kuat berventilasi udara dan bekal pakan 2 hari.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-shield-shaded text-info mt-1"></i>
                        <span><strong>Vaksin Lengkap:</strong> Ayam sudah divaksin ND, IB, dan Marek sebelum dikirim.</span>
                    </li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="fs-6">Ekspedisi Rekanan</h5>
                <p class="small mb-3">Melayani pengiriman seluruh Pulau Jawa, Sumatera, Bali, dan Kalimantan via jalur resmi kargo hewan:</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-secondary bg-opacity-50 text-white px-2 py-1"><i class="bi bi-train-front me-1"></i> KIB Cepat (Kereta)</span>
                    <span class="badge bg-secondary bg-opacity-50 text-white px-2 py-1"><i class="bi bi-train-freight-front me-1"></i> Kalog Express</span>
                    <span class="badge bg-secondary bg-opacity-50 text-white px-2 py-1"><i class="bi bi-bus-front me-1"></i> Bus Safari &amp; Agen</span>
                    <span class="badge bg-secondary bg-opacity-50 text-white px-2 py-1"><i class="bi bi-airplane me-1"></i> Kargo Udara Port-to-Port</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong> &mdash; <?= e($tagline) ?>. All rights reserved.
            </div>
            <div class="small text-white-50">
                Aplikasi E-Commerce Peternakan Modern &middot; PHP Native, MySQLi &amp; Bootstrap 5.3
            </div>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

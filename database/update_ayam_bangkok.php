<?php
/**
 * Script migrasi otomatis untuk mengupdate produk dan kategori menjadi Ayam Ternak & Bangkok.
 * Jalankan file ini melalui browser di http://localhost/web_penjualanAyam/database/update_ayam_bangkok.php
 * atau via CLI: php update_ayam_bangkok.php
 */
require_once __DIR__ . '/../config/koneksi.php';

echo "<h2>Migrasi Data Ayam Bangkok & Ternak</h2>";

// 1. Update atau insert kategori
$kategori_data = [
    1 => 'Ayam Bangkok & Aduan',
    2 => 'Ayam Trah Import (Pakhoy, Birma, Shamo)',
    3 => 'Ayam Ternak & Kampung Super',
    4 => 'Bibit DOC & Indukan Berkualitas',
    5 => 'Pakan, Jamu & Perlengkapan Ternak'
];

foreach ($kategori_data as $id => $nama) {
    $stmt = $koneksi->prepare("INSERT INTO kategori (id_kategori, nama_kategori) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama_kategori = VALUES(nama_kategori)");
    $stmt->bind_param("is", $id, $nama);
    $stmt->execute();
    $stmt->close();
}
echo "<p>✅ Kategori berhasil diperbarui.</p>";

// 2. Kosongkan produk lama dan masukkan produk ayam bangkok
$koneksi->query("SET FOREIGN_KEY_CHECKS = 0");
$koneksi->query("TRUNCATE TABLE produk");
$koneksi->query("SET FOREIGN_KEY_CHECKS = 1");

$produk_data = [
    [
        'id_penjual' => 1,
        'id_kategori' => 1,
        'nama' => 'Ayam Bangkok Super Wiring Kuning (Pejantan F1)',
        'deskripsi' => 'Pejantan Bangkok Super Wiring Kuning trah juara, usia 11 bulan dengan bobot 3.4 kg. Tulangan rapat kokoh, kepala buah pinang, leher lentur pukulan saraf akurat menyasar vital lawan. Vaksin lengkap (ND, IB, Gumboro), mental agresif petarung sejati. Garansi sehat dan hidup saat pengiriman dalam kotak kargo kayu.',
        'harga' => 1850000,
        'stok' => 5,
        'gambar' => 'bangkok_wiring_kuning.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 1,
        'nama' => 'Ayam Bangkok Wangkas Emas Super',
        'deskripsi' => 'Ayam Bangkok Wangkas Emas dengan bulu tembaga mengkilap lebat, mata kristal tajam, paruh tebal kokoh, postur tegap gagah. Usia 10.5 bulan, berat 3.3 kg. Memiliki pukulan berat beruntun dan teknik ngunci rapat. Siap latih lanjut untuk calon jawara.',
        'harga' => 1650000,
        'stok' => 4,
        'gambar' => 'bangkok_wangkas.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 2,
        'nama' => 'Ayam Pakhoy Import Super Speed & Knockout',
        'deskripsi' => 'Trah Pakhoy (Pakpoy) darah impor asli generasi F1. Gaya bertarung sangat cepat, ngolong rapat agresif, pukulan punggung, sayap, dan leher sangat keras mematikan. Usia 9.5 bulan, berat 3.2 kg, fisik berotot dan lincah bertenaga.',
        'harga' => 2400000,
        'stok' => 3,
        'gambar' => 'ayam_pakhoy.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 2,
        'nama' => 'Ayam Birma (Burma) Gostan Taji Akurat',
        'deskripsi' => 'Ayam Birma murni teknik gostan (mundur tembak), pukulan paruh dan taji sangat cepat dan akurat ke mata dan kuping lawan. Tulang padat ringan dengan gerakan lincah menghindari pukulan rapat. Usia 10 bulan, berat 2.9 kg.',
        'harga' => 1750000,
        'stok' => 6,
        'gambar' => 'ayam_birma.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 2,
        'nama' => 'Ayam Shamo Jepang Pejantan Raksasa 4.1 Kg',
        'deskripsi' => 'Ayam Shamo trah murni Jepang dengan tinggi postur menjulang dan dada bidang kekar. Berat 4.1 kg, tulang sebesar pergelangan tangan, daya tahan pukul luar biasa. Sangat istimewa untuk materi pemacek indukan jantan di farm.',
        'harga' => 3200000,
        'stok' => 2,
        'gambar' => 'ayam_shamo.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 4,
        'nama' => 'Babon Bangkok Indukan Birahi Cetak Juara',
        'deskripsi' => 'Indukan (babon) Bangkok pilihan darah juara trah Wiring Kuning x Pakhoy. Tulang supit rapat, ekor lebat rapat lurus, posisi birahi siap bertelur. Memiliki genetik kuat menurunkan anakan bermental baja dan bertulang tebal.',
        'harga' => 950000,
        'stok' => 8,
        'gambar' => 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 3,
        'nama' => 'Bibit DOC Ayam Kampung Super (Joper) Box 100 Ekor',
        'deskripsi' => 'Bibit DOC Joper (Jowo Super) sehat lincah grade A kemasan box isi 100 ekor + bonus 2 ekor cadangan. Sudah divaksin Marek di hatchery, pertumbuhan cepat 60 hari panen bobot 0.9 - 1.1 kg. Garansi kematian perjalanan disertakan.',
        'harga' => 680000,
        'stok' => 25,
        'gambar' => 'doc_ayam_kampung.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 3,
        'nama' => 'Ayam Kampung Asli Pejantan Jago Bobot 2.8 Kg',
        'deskripsi' => 'Ayam jago kampung asli sehat dipelihara semi-umbaran alami. Sehat bebas antibiotik kimia berlebih, bulu cerah berkilau, cocok untuk pejantan ternak kampung maupun konsumsi acara hajatan istimewa.',
        'harga' => 185000,
        'stok' => 18,
        'gambar' => 'https://images.unsplash.com/photo-1612170153139-6f881ff067e0?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 4,
        'nama' => 'Telur Fertil Tetas Ayam Bangkok F1 (Isi 10 Butir)',
        'deskripsi' => 'Telur fertil hasil perkawinan kandang battery pacek Bangkok Super x babon Pakhoy F1. Garansi embrio aktif 85%, dikemas spons tebal anti benturan khusus kirim luar kota aman sampai tujuan.',
        'harga' => 350000,
        'stok' => 15,
        'gambar' => 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 5,
        'nama' => 'Jamu Herbal Ayam Jago Champion Formula 100 Kapsul',
        'deskripsi' => 'Suplemen herbal khusus ayam aduan dan ternak terbuat dari ekstrak pasak bumi, madu hutan, ginseng merah, dan temu lawak. Memperpanjang nafas saat abar, memperkuat otot leher dan kaki, serta menambah nafsu makan dan daya tahan penyakit.',
        'harga' => 85000,
        'stok' => 50,
        'gambar' => 'jamu_ayam.jpg'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 5,
        'nama' => 'Pakan Pur Hi-Pro Konsentrat Ayam Jago 5 Kg',
        'deskripsi' => 'Pakan butiran formula tinggi kalsium dan protein 21% untuk pembentukan otot padat tanpa lemak, memperkuat sisik cakar dan kepadatan paruh ayam jago dan ayam pejantan ternak.',
        'harga' => 95000,
        'stok' => 40,
        'gambar' => 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id_penjual' => 1,
        'id_kategori' => 5,
        'nama' => 'Kiso Tas Ayam Bambu Rajut Tradisional Premium',
        'deskripsi' => 'Kiso tempat membawa ayam jago terbuat dari rajutan bambu tua yang halus dan kokoh dilengkapi tali selempang nyaman. Ventilasi udara maksimal sehingga ayam tidak stres dan bulu ekor tidak rusak/patah.',
        'harga' => 125000,
        'stok' => 30,
        'gambar' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80'
    ]
];

$stmt = $koneksi->prepare("INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($produk_data as $p) {
    $stmt->bind_param("iissdis", $p['id_penjual'], $p['id_kategori'], $p['nama'], $p['deskripsi'], $p['harga'], $p['stok'], $p['gambar']);
    $stmt->execute();
}
$stmt->close();

echo "<p>✅ 12 Produk Ayam Bangkok & Ternak berhasil dimasukkan ke database!</p>";
echo "<p><a href='../index.php' style='padding: 8px 16px; background: #166534; color: white; border-radius: 6px; text-decoration: none;'>Kembali ke Beranda</a></p>";

<?php
/**
 * Auto-migration helper for BangkokFarm.
 * Runs automatically if categories do not contain Ayam Bangkok.
 */
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    require_once __DIR__ . '/../config/koneksi.php';
}

function jalankan_migrasi_ayam_bangkok(mysqli $koneksi)
{
    // Cek apakah sudah pernah dimigrasi
    $res = $koneksi->query("SELECT COUNT(*) AS c FROM kategori WHERE nama_kategori LIKE '%Bangkok%'");
    if ($res) {
        $row = $res->fetch_assoc();
        if ((int)($row['c'] ?? 0) > 0) {
            // Sudah ada kategori bangkok, cek apakah produk ada
            $res_p = $koneksi->query("SELECT COUNT(*) AS cp FROM produk WHERE nama_produk LIKE '%Bangkok%'");
            if ($res_p) {
                $row_p = $res_p->fetch_assoc();
                if ((int)($row_p['cp'] ?? 0) > 0) {
                    return; // Sudah migrasi lengkap
                }
            }
        }
    }

    // 1. Kategori Baru
    $kategori_data = [
        1 => 'Ayam Bangkok & Aduan',
        2 => 'Ayam Trah Import (Pakhoy, Birma, Shamo)',
        3 => 'Ayam Ternak & Kampung Super',
        4 => 'Bibit DOC & Indukan Berkualitas',
        5 => 'Pakan, Jamu & Perlengkapan Ternak'
    ];

    foreach ($kategori_data as $id => $nama) {
        $stmt = $koneksi->prepare("INSERT INTO kategori (id_kategori, nama_kategori) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama_kategori = VALUES(nama_kategori)");
        if ($stmt) {
            $stmt->bind_param("is", $id, $nama);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Hapus kategori lama yang tidak terpakai
    $koneksi->query("DELETE FROM kategori WHERE id_kategori NOT IN (1, 2, 3, 4, 5)");

    // 2. Bersihkan produk lama
    $koneksi->query("SET FOREIGN_KEY_CHECKS = 0");
    $koneksi->query("DELETE FROM keranjang");
    $koneksi->query("DELETE FROM produk");
    $koneksi->query("SET FOREIGN_KEY_CHECKS = 1");

    // 3. Masukkan 12 Produk Ayam Bangkok & Ternak
    $produk_data = [
        [
            'id_penjual' => 1,
            'id_kategori' => 1,
            'nama' => 'Ayam Bangkok Super Wiring Kuning (Pejantan F1)',
            'deskripsi' => "Pejantan Bangkok Super Wiring Kuning trah juara, usia 11 bulan dengan bobot 3.4 kg.\nTulangan rapat kokoh, kepala buah pinang, leher lentur pukulan saraf akurat menyasar vital lawan.\nVaksin lengkap (ND, IB, Gumboro), mental agresif petarung sejati.\nGaransi sehat dan hidup saat pengiriman dalam kotak kargo kayu.",
            'harga' => 1850000,
            'stok' => 5,
            'gambar' => 'bangkok_wiring_kuning.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 1,
            'nama' => 'Ayam Bangkok Wangkas Emas Super',
            'deskripsi' => "Ayam Bangkok Wangkas Emas dengan bulu tembaga mengkilap lebat, mata kristal tajam, paruh tebal kokoh, postur tegap gagah.\nUsia 10.5 bulan, berat 3.3 kg. Memiliki pukulan berat beruntun dan teknik ngunci rapat.\nSiap latih lanjut untuk calon jawara.",
            'harga' => 1650000,
            'stok' => 4,
            'gambar' => 'bangkok_wangkas.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 2,
            'nama' => 'Ayam Pakhoy Import Super Speed & Knockout',
            'deskripsi' => "Trah Pakhoy (Pakpoy) darah impor asli generasi F1.\nGaya bertarung sangat cepat, ngolong rapat agresif, pukulan punggung, sayap, dan leher sangat keras mematikan.\nUsia 9.5 bulan, berat 3.2 kg, fisik berotot dan lincah bertenaga.",
            'harga' => 2400000,
            'stok' => 3,
            'gambar' => 'ayam_pakhoy.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 2,
            'nama' => 'Ayam Birma (Burma) Gostan Taji Akurat',
            'deskripsi' => "Ayam Birma murni teknik gostan (mundur tembak), pukulan paruh dan taji sangat cepat dan akurat ke mata dan kuping lawan.\nTulang padat ringan dengan gerakan lincah menghindari pukulan rapat.\nUsia 10 bulan, berat 2.9 kg.",
            'harga' => 1750000,
            'stok' => 6,
            'gambar' => 'ayam_birma.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 2,
            'nama' => 'Ayam Shamo Jepang Pejantan Raksasa 4.1 Kg',
            'deskripsi' => "Ayam Shamo trah murni Jepang dengan tinggi postur menjulang dan dada bidang kekar.\nBerat 4.1 kg, tulang sebesar pergelangan tangan, daya tahan pukul luar biasa.\nSangat istimewa untuk materi pemacek indukan jantan di farm.",
            'harga' => 3200000,
            'stok' => 2,
            'gambar' => 'ayam_shamo.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 4,
            'nama' => 'Babon Bangkok Indukan Birahi Cetak Juara',
            'deskripsi' => "Indukan (babon) Bangkok pilihan darah juara trah Wiring Kuning x Pakhoy.\nTulang supit rapat, ekor lebat rapat lurus, posisi birahi siap bertelur.\nMemiliki genetik kuat menurunkan anakan bermental baja dan bertulang tebal.",
            'harga' => 950000,
            'stok' => 8,
            'gambar' => 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 3,
            'nama' => 'Bibit DOC Ayam Kampung Super (Joper) Box 100 Ekor',
            'deskripsi' => "Bibit DOC Joper (Jowo Super) sehat lincah grade A kemasan box isi 100 ekor + bonus 2 ekor cadangan.\nSudah divaksin Marek di hatchery, pertumbuhan cepat 60 hari panen bobot 0.9 - 1.1 kg.\nGaransi kematian perjalanan disertakan.",
            'harga' => 680000,
            'stok' => 25,
            'gambar' => 'doc_ayam_kampung.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 3,
            'nama' => 'Ayam Kampung Asli Pejantan Jago Bobot 2.8 Kg',
            'deskripsi' => "Ayam jago kampung asli sehat dipelihara semi-umbaran alami.\nSehat bebas antibiotik kimia berlebih, bulu cerah berkilau, cocok untuk pejantan ternak kampung maupun konsumsi acara hajatan istimewa.",
            'harga' => 185000,
            'stok' => 18,
            'gambar' => 'https://images.unsplash.com/photo-1612170153139-6f881ff067e0?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 4,
            'nama' => 'Telur Fertil Tetas Ayam Bangkok F1 (Isi 10 Butir)',
            'deskripsi' => "Telur fertil hasil perkawinan kandang battery pacek Bangkok Super x babon Pakhoy F1.\nGaransi embrio aktif 85%, dikemas spons tebal anti benturan khusus kirim luar kota aman sampai tujuan.",
            'harga' => 350000,
            'stok' => 15,
            'gambar' => 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 5,
            'nama' => 'Jamu Herbal Ayam Jago Champion Formula 100 Kapsul',
            'deskripsi' => "Suplemen herbal khusus ayam aduan dan ternak terbuat dari ekstrak pasak bumi, madu hutan, ginseng merah, dan temu lawak.\nMemperpanjang nafas saat abar, memperkuat otot leher dan kaki, serta menambah nafsu makan dan daya tahan penyakit.",
            'harga' => 85000,
            'stok' => 50,
            'gambar' => 'jamu_ayam.jpg'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 5,
            'nama' => 'Pakan Pur Hi-Pro Konsentrat Ayam Jago 5 Kg',
            'deskripsi' => "Pakan butiran formula tinggi kalsium dan protein 21% untuk pembentukan otot padat tanpa lemak, memperkuat sisik cakar dan kepadatan paruh ayam jago dan ayam pejantan ternak.",
            'harga' => 95000,
            'stok' => 40,
            'gambar' => 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id_penjual' => 1,
            'id_kategori' => 5,
            'nama' => 'Kiso Tas Ayam Bambu Rajut Tradisional Premium',
            'deskripsi' => "Kiso tempat membawa ayam jago terbuat dari rajutan bambu tua yang halus dan kokoh dilengkapi tali selempang nyaman.\nVentilasi udara maksimal sehingga ayam tidak stres dan bulu ekor tidak rusak/patah.",
            'harga' => 125000,
            'stok' => 30,
            'gambar' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80'
        ]
    ];

    $stmt = $koneksi->prepare("INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        foreach ($produk_data as $p) {
            $stmt->bind_param("iissdis", $p['id_penjual'], $p['id_kategori'], $p['nama'], $p['deskripsi'], $p['harga'], $p['stok'], $p['gambar']);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// Jalankan jika koneksi aktif
if (isset($koneksi) && $koneksi instanceof mysqli) {
    jalankan_migrasi_ayam_bangkok($koneksi);
}

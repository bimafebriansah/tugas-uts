-- =====================================================================
-- SOAL 11 - APLIKASI WEB MARKETPLACE TERNAK (BangkokFarm)
-- Sentra Jual Beli Ayam Bangkok, Ayam Laga Trah Unggul & Ayam Ternak
-- Skema Database Relasional + Data Contoh (Seed)
-- Engine: InnoDB | Charset: utf8mb4
-- Import file ini melalui phpMyAdmin (XAMPP) atau MySQL CLI
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_marketplace_ayam
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;
USE db_marketplace_ayam;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS keranjang;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. ENTITAS: users (pengguna: admin / user)
--    admin = mengelola produk & pesanan
--    user  = pembeli (beli produk, lihat pesanan)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id_user     INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','user') NOT NULL DEFAULT 'user',
    telepon     VARCHAR(20)  DEFAULT NULL,
    alamat      TEXT         DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. ENTITAS: kategori (kategori ayam ternak & laga)
-- ---------------------------------------------------------------------
CREATE TABLE kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. ENTITAS: produk (ayam ternak, bangkok, pakan & perlengkapan)
--    FK -> users(id_user) [admin], FK -> kategori(id_kategori)
-- ---------------------------------------------------------------------
CREATE TABLE produk (
    id_produk   INT AUTO_INCREMENT PRIMARY KEY,
    id_penjual  INT NOT NULL,
    id_kategori INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi   TEXT         DEFAULT NULL,
    harga       DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok        INT           NOT NULL DEFAULT 0,
    gambar      VARCHAR(255)  DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produk_penjual  FOREIGN KEY (id_penjual)  REFERENCES users(id_user)      ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_produk_kategori FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. ENTITAS: keranjang (item keranjang belanja pembeli)
--    FK -> users(id_user), FK -> produk(id_produk)
-- ---------------------------------------------------------------------
CREATE TABLE keranjang (
    id_keranjang INT AUTO_INCREMENT PRIMARY KEY,
    id_user      INT NOT NULL,
    id_produk    INT NOT NULL,
    qty          INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_keranjang_user   FOREIGN KEY (id_user)   REFERENCES users(id_user)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_keranjang_produk FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_keranjang (id_user, id_produk)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. ENTITAS: pesanan (header transaksi pemesanan)
--    FK -> users(id_user) [pembeli]
-- ---------------------------------------------------------------------
CREATE TABLE pesanan (
    id_pesanan    INT AUTO_INCREMENT PRIMARY KEY,
    id_pembeli    INT NOT NULL,
    tanggal       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total         DECIMAL(12,2) NOT NULL DEFAULT 0,
    status        ENUM('pending','diproses','dikirim','selesai','batal') NOT NULL DEFAULT 'pending',
    nama_penerima VARCHAR(100) NOT NULL,
    telepon       VARCHAR(20)  DEFAULT NULL,
    alamat_kirim  TEXT         NOT NULL,
    CONSTRAINT fk_pesanan_pembeli FOREIGN KEY (id_pembeli) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. ENTITAS: detail_pesanan (baris produk tiap pesanan)
--    FK -> pesanan(id_pesanan), FK -> produk(id_produk)
-- ---------------------------------------------------------------------
CREATE TABLE detail_pesanan (
    id_detail    INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan   INT NOT NULL,
    id_produk    INT NOT NULL,
    qty          INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    subtotal     DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_produk  FOREIGN KEY (id_produk)  REFERENCES produk(id_produk)   ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- DATA CONTOH (SEED)
-- Akun demo (password sudah di-hash dengan password_hash / bcrypt):
--   admin : admin@ayammart.test  / admin123   (mengelola produk & pesanan)
--   user  : user@ayammart.test   / user123    (pembeli)
-- =====================================================================
INSERT INTO users (nama, email, password, role, telepon, alamat) VALUES
('Administrator Farm', 'admin@ayammart.test', '$2y$10$5U2oT6CwReCNGftYS7.A/uOZDG2Z/oLqKCMNAMpHtZQ6fH6qa0fbe', 'admin', '081288997766', 'BangkokFarm Center, Jl. Raya Peternakan No. 88, Bogor'),
('Budi Peternak',       'user@ayammart.test',  '$2y$10$iS9AfyPTFPRDaXNb6f.Mxu7H4RDU8iZQlisjnDBdlgzrmZTBMbZHy', 'user',  '081299887711', 'Jl. Kenangan No. 10, Bandung');

INSERT INTO kategori (id_kategori, nama_kategori) VALUES
(1, 'Ayam Bangkok & Aduan'),
(2, 'Ayam Trah Import (Pakhoy, Birma, Shamo)'),
(3, 'Ayam Ternak & Kampung Super'),
(4, 'Bibit DOC & Indukan Berkualitas'),
(5, 'Pakan, Jamu & Perlengkapan Ternak');

INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar) VALUES
(1, 1, 'Ayam Bangkok Super Wiring Kuning (Pejantan F1)', 'Pejantan Bangkok Super Wiring Kuning trah juara, usia 11 bulan dengan bobot 3.4 kg. Tulangan rapat kokoh, kepala buah pinang, leher lentur pukulan saraf akurat menyasar vital lawan. Vaksin lengkap (ND, IB, Gumboro), mental agresif petarung sejati. Garansi sehat dan hidup saat pengiriman dalam kotak kargo kayu.', 1850000, 5, 'bangkok_wiring_kuning.jpg'),
(1, 1, 'Ayam Bangkok Wangkas Emas Super', 'Ayam Bangkok Wangkas Emas dengan bulu tembaga mengkilap lebat, mata kristal tajam, paruh tebal kokoh, postur tegap gagah. Usia 10.5 bulan, berat 3.3 kg. Memiliki pukulan berat beruntun dan teknik ngunci rapat. Siap latih lanjut untuk calon jawara.', 1650000, 4, 'bangkok_wangkas.jpg'),
(1, 2, 'Ayam Pakhoy Import Super Speed & Knockout', 'Trah Pakhoy (Pakpoy) darah impor asli generasi F1. Gaya bertarung sangat cepat, ngolong rapat agresif, pukulan punggung, sayap, dan leher sangat keras mematikan. Usia 9.5 bulan, berat 3.2 kg, fisik berotot dan lincah bertenaga.', 2400000, 3, 'ayam_pakhoy.jpg'),
(1, 2, 'Ayam Birma (Burma) Gostan Taji Akurat', 'Ayam Birma murni teknik gostan (mundur tembak), pukulan paruh dan taji sangat cepat dan akurat ke mata dan kuping lawan. Tulang padat ringan dengan gerakan lincah menghindari pukulan rapat. Usia 10 bulan, berat 2.9 kg.', 1750000, 6, 'ayam_birma.jpg'),
(1, 2, 'Ayam Shamo Jepang Pejantan Raksasa 4.1 Kg', 'Ayam Shamo trah murni Jepang dengan tinggi postur menjulang dan dada bidang kekar. Berat 4.1 kg, tulang sebesar pergelangan tangan, daya tahan pukul luar biasa. Sangat istimewa untuk materi pemacek indukan jantan di farm.', 3200000, 2, 'ayam_shamo.jpg'),
(1, 4, 'Babon Bangkok Indukan Birahi Cetak Juara', 'Indukan (babon) Bangkok pilihan darah juara trah Wiring Kuning x Pakhoy. Tulang supit rapat, ekor lebat rapat lurus, posisi birahi siap bertelur. Memiliki genetik kuat menurunkan anakan bermental baja dan bertulang tebal.', 950000, 8, 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=600&q=80'),
(1, 3, 'Bibit DOC Ayam Kampung Super (Joper) Box 100 Ekor', 'Bibit DOC Joper (Jowo Super) sehat lincah grade A kemasan box isi 100 ekor + bonus 2 ekor cadangan. Sudah divaksin Marek di hatchery, pertumbuhan cepat 60 hari panen bobot 0.9 - 1.1 kg. Garansi kematian perjalanan disertakan.', 680000, 25, 'doc_ayam_kampung.jpg'),
(1, 3, 'Ayam Kampung Asli Pejantan Jago Bobot 2.8 Kg', 'Ayam jago kampung asli sehat dipelihara semi-umbaran alami. Sehat bebas antibiotik kimia berlebih, bulu cerah berkilau, cocok untuk pejantan ternak kampung maupun konsumsi acara hajatan istimewa.', 185000, 18, 'https://images.unsplash.com/photo-1612170153139-6f881ff067e0?auto=format&fit=crop&w=600&q=80'),
(1, 4, 'Telur Fertil Tetas Ayam Bangkok F1 (Isi 10 Butir)', 'Telur fertil hasil perkawinan kandang battery pacek Bangkok Super x babon Pakhoy F1. Garansi embrio aktif 85%, dikemas spons tebal anti benturan khusus kirim luar kota aman sampai tujuan.', 350000, 15, 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?auto=format&fit=crop&w=600&q=80'),
(1, 5, 'Jamu Herbal Ayam Jago Champion Formula 100 Kapsul', 'Suplemen herbal khusus ayam aduan dan ternak terbuat dari ekstrak pasak bumi, madu hutan, ginseng merah, dan temu lawak. Memperpanjang nafas saat abar, memperkuat otot leher dan kaki, serta menambah nafsu makan dan daya tahan penyakit.', 85000, 50, 'jamu_ayam.jpg'),
(1, 5, 'Pakan Pur Hi-Pro Konsentrat Ayam Jago 5 Kg', 'Pakan butiran formula tinggi kalsium dan protein 21% untuk pembentukan otot padat tanpa lemak, memperkuat sisik cakar dan kepadatan paruh ayam jago dan ayam pejantan ternak.', 95000, 40, 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=600&q=80'),
(1, 5, 'Kiso Tas Ayam Bambu Rajut Tradisional Premium', 'Kiso tempat membawa ayam jago terbuat dari rajutan bambu tua yang halus dan kokoh dilengkapi tali selempang nyaman. Ventilasi udara maksimal sehingga ayam tidak stres dan bulu ekor tidak rusak/patah.', 125000, 30, 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80');

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 05, 2026 at 07:37 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_dpwl_keshia123`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_admin`
--

CREATE TABLE `t_admin` (
  `username` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `password` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `status_akun` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'nonaktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_admin`
--

INSERT INTO `t_admin` (`username`, `password`, `status_akun`) VALUES
('admin', '$2y$10$D1cxossakasrdosrVNmuy.M1l/wxlHkGNxihQr1W8k6dZ5HtArHiq', 'aktif'),
('keshia', '$2y$10$EZLVI2VTAPETmNKc3x3U1OWotXmI/Nf3P796UUK89.zoLWLe5IFAm', 'aktif'),
('Keshia Minerva', '$2y$10$Ds/MSBwpE.WQ72HLWC8CUOqbNamtFZnRsLwCKoAijsKk21e.CkWN2', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `t_bayar`
--

CREATE TABLE `t_bayar` (
  `kode_bayar` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tgl_wkt_byr` datetime NOT NULL,
  `ft_byr` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `mtode_byr` enum('tunai','transfer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `jns_byr` enum('dp','pelunasan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `nominal` mediumint NOT NULL,
  `sttus_byr` enum('pending','valid','invalid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tgl_wkt_ver` datetime DEFAULT NULL,
  `kode_sewa` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_bayar`
--

INSERT INTO `t_bayar` (`kode_bayar`, `tgl_wkt_byr`, `ft_byr`, `mtode_byr`, `jns_byr`, `nominal`, `sttus_byr`, `tgl_wkt_ver`, `kode_sewa`) VALUES
('PAY/20260702/01', '2026-07-02 13:56:47', NULL, 'tunai', 'dp', 270000, 'valid', '2026-07-02 13:56:47', 'INV/20260702/01'),
('PAY/20260702/02', '2026-07-02 20:49:08', NULL, 'tunai', 'dp', 560000, 'valid', '2026-07-02 20:49:08', 'INV/20260702/02'),
('PAY/20260702/03', '2026-07-02 21:29:08', NULL, 'tunai', 'pelunasan', 840000, 'valid', '2026-07-02 21:29:08', 'INV/20260702/02'),
('PAY/20260703/01', '2026-07-03 15:23:58', NULL, 'tunai', 'pelunasan', 630000, 'valid', '2026-07-03 15:23:58', 'INV/20260702/01'),
('PAY/20260704/01', '2026-07-04 09:38:33', 'ft_byr_1783132725_6a487235b75a0.jpeg', 'transfer', 'dp', 250000, 'valid', '2026-07-04 09:40:39', 'INV/20260704/01'),
('PAY/20260704/02', '2026-07-04 09:46:48', NULL, 'tunai', 'pelunasan', 110000, 'valid', '2026-07-04 09:46:48', 'INV/20260704/01'),
('PAY/20260704/03', '2026-07-04 14:20:54', 'ft_byr_1783150294_6a48b6d69576d.jpeg', 'transfer', 'dp', 150000, 'pending', NULL, 'INV/20260704/02'),
('PAY/20260705/01', '2026-07-05 00:10:21', 'ft_byr_1783185669_6a494105c0eee.jpeg', 'transfer', 'dp', 150000, 'pending', NULL, 'INV/20260705/01'),
('PAY/20260705/02', '2026-07-05 00:57:55', 'ft_byr_1783187883_6a4949abd1077.jpeg', 'transfer', 'dp', 150000, 'valid', '2026-07-05 01:03:04', 'INV/20260705/02'),
('PAY/20260706/01', '2026-07-06 14:09:35', NULL, 'tunai', 'pelunasan', 500000, 'valid', '2026-07-06 14:09:35', 'INV/20260703/01'),
('PAY/20261031/01', '2026-10-31 22:37:11', NULL, 'transfer', 'dp', 360000, 'pending', NULL, 'INV/20261031/01'),
('PAY/20261102/01', '2026-11-02 14:56:23', NULL, 'transfer', 'dp', 240000, 'pending', NULL, 'INV/20261031/02'),
('PAY/20261102/02', '2026-11-02 15:21:01', NULL, 'transfer', 'dp', 240000, 'pending', NULL, 'INV/20261102/01'),
('PAY/20261102/03', '2026-11-02 15:56:08', NULL, 'transfer', 'dp', 240000, 'pending', NULL, 'INV/20261102/03');

-- --------------------------------------------------------

--
-- Table structure for table `t_denda`
--

CREATE TABLE `t_denda` (
  `kode_denda` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `telat_jam` tinyint(1) NOT NULL,
  `tarif_per_jam` mediumint NOT NULL,
  `kode_kembali` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_denda`
--

INSERT INTO `t_denda` (`kode_denda`, `telat_jam`, `tarif_per_jam`, `kode_kembali`) VALUES
('FINE/20260703/1', 1, 50000, 'RET/20260703/01'),
('FINE/20260703/2', 3, 50000, 'RET/20260703/02'),
('FINE/20260703/3', 2, 25000, 'RET/20260702/01'),
('FINE/20260704/1', 3, 0, 'RET/20260704/01');

-- --------------------------------------------------------

--
-- Table structure for table `t_kembali`
--

CREATE TABLE `t_kembali` (
  `kode_kembali` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tgl_wkt_blk` datetime NOT NULL,
  `stts_blk` enum('normal','terlambat') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'normal',
  `ket_blk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `kode_bayar` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_kembali`
--

INSERT INTO `t_kembali` (`kode_kembali`, `tgl_wkt_blk`, `stts_blk`, `ket_blk`, `kode_bayar`) VALUES
('RET/20260702/01', '2026-07-02 21:30:00', 'terlambat', 'rese', 'PAY/20260702/03'),
('RET/20260703/01', '2026-07-03 15:47:00', 'terlambat', 'mobil sedikit kotor', 'PAY/20260703/01'),
('RET/20260703/02', '2026-07-03 23:11:00', 'terlambat', 'telat 3 jam', 'PAY/20260703/02'),
('RET/20260704/01', '2026-07-04 09:46:00', 'terlambat', 'bensin tidak diisi full', 'PAY/20260704/02'),
('RET/20260706/01', '2026-07-06 14:10:00', 'normal', '', 'PAY/20260706/01');

-- --------------------------------------------------------

--
-- Table structure for table `t_mobil`
--

CREATE TABLE `t_mobil` (
  `no_plat` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nm_mobil` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nkursi` tinyint(1) NOT NULL,
  `nbagasi` tinyint(1) NOT NULL,
  `jns_mobil` enum('SUV','MPV','City Car') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `transmisi` enum('AT','MT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `thn_buat` year NOT NULL,
  `ft_depan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `ft_blkg` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `ft_interior` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `hrg_hari` mediumint NOT NULL,
  `cttn_unit` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `status_unit` enum('tersedia','disewa','tidaktersedia') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_mobil`
--

INSERT INTO `t_mobil` (`no_plat`, `nm_mobil`, `nkursi`, `nbagasi`, `jns_mobil`, `transmisi`, `thn_buat`, `ft_depan`, `ft_blkg`, `ft_interior`, `hrg_hari`, `cttn_unit`, `status_unit`) VALUES
('B 1173 HFF', 'Toyota Kijang Innova Reborn 2.4 G Diesel', 7, 2, 'MPV', 'AT', '2025', 'ft_depan_20261026071954_931e83dd0a8a8fd2.jpg', 'ft_blkg_20261026071954_3daa6d833d46eb07.jpg', 'ft_interior_20261026071954_ebb7495cb0fd1298.jpg', 700000, 'Toyota Kijang Innova Reborn 2.4 G Diesel tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('B 1583 RKE', 'Honda Brio 1.2 RS CVT CKD', 5, 2, 'City Car', 'AT', '2023', '', '', '', 400000, 'Honda Brio 1.2 RS CVT CKD tahun 2023 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('B 4 LIP', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2020', '', '', '', 650000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2020 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1000 OK', 'Mazda 3', 5, 2, 'SUV', 'AT', '2018', 'ft_depan_20261022070948_dc51e53d73ecb3ea.jpg', 'ft_blkg_20261022070948_45617fb291df8cf8.jpg', 'ft_interior_20261022070948_050f9272a5d8ec7d.jpg', 800000, 'Unit mulus', 'tersedia'),
('BN 1020 AC', 'Honda Brio 1.2 RS CVT', 5, 2, 'City Car', 'AT', '2021', '', '', '', 370000, 'Honda Brio 1.2 RS CVT tahun 2021 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1058 AN', 'Toyota Kijang Innova Reborn 2.0 G', 7, 2, 'MPV', 'AT', '2020', '', '', '', 550000, 'Toyota Kijang Innova Reborn 2.0 G tahun 2020 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1060 LZQ', 'Toyota Kijang Innova Reborn 2.4 G Diesel', 7, 2, 'MPV', 'AT', '2025', '', '', '', 700000, 'Toyota Kijang Innova Reborn 2.4 G Diesel tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1070 VKT', 'Toyota Kijang Innova Reborn 2.4 G Diesel', 7, 2, 'MPV', 'AT', '2025', '', '', '', 700000, 'Toyota Kijang Innova Reborn 2.4 G Diesel tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1078 AU', 'Toyota Kijang Innova Reborn 2.4 G Diesel', 7, 2, 'MPV', 'AT', '2023', '', '', '', 680000, 'Toyota Kijang Innova Reborn 2.4 G Diesel tahun 2023 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1142 PJ', 'Toyota Kijang Innova Reborn 2.0 V', 7, 2, 'MPV', 'AT', '2021', '', '', '', 600000, 'Toyota Kijang Innova Reborn 2.0 V tahun 2021 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1169 TE', 'Honda Brio 1.2 RS CVT', 5, 2, 'City Car', 'AT', '2025', '', '', '', 450000, 'Honda Brio 1.2 RS CVT tahun 2025 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1194 AB', 'Honda BR-V 1.5 CVT (All New Honda BR-V)', 7, 2, 'SUV', 'AT', '2022', 'ft_depan_1782637173_6a40e275cdf17.jpeg', 'ft_blkg_1782637173_6a40e275ce2ce.jpg', 'ft_interior_1782637173_6a40e275ce5f4.jpg', 500000, 'Honda BR-V 1.5 CVT tahun 2022 merupakan kendaraan kategori SUV Crossover berkapasitas 7 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.5 liter. Kendaraan ini menawarkan kabin yang luas, posisi berkendara yang nyaman, serta ground clearance yang tinggi sehingga cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1362 QG', 'Toyota Kijang Innova Reborn 2.0 V', 7, 2, 'MPV', 'AT', '2022', '', '', '', 620000, 'Toyota Kijang Innova Reborn 2.0 V tahun 2022 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1460 AD', 'Honda Brio 1.2 Satya E CVT', 5, 2, 'City Car', 'AT', '2023', 'ft_depan_1782633878_6a40d596adbe7.jpeg', 'ft_blkg_1782636359_6a40df470f445.jpeg', 'ft_interior_1782636359_6a40df470f762.jpg', 380000, 'Honda Brio 1.2 Satya E CVT tahun 2023 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1467 AV', 'Toyota Kijang Innova Reborn 2.0 G', 7, 2, 'MPV', 'AT', '2020', '', '', '', 550000, 'Toyota Kijang Innova Reborn 2.0 G tahun 2020 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1470 AA', 'Toyota Kijang Innova Reborn 2.0 G', 7, 2, 'MPV', 'AT', '2022', '', '', '', 580000, 'Toyota Kijang Innova Reborn 2.0 G tahun 2022 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1570 AC', 'Honda Brio 1.2 RS CVT', 5, 2, 'City Car', 'AT', '2024', '', '', '', 420000, 'Honda Brio 1.2 RS CVT tahun 2024 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1639 AA', 'Honda Brio 1.2 Satya E CVT', 5, 2, 'City Car', 'AT', '2020', '', '', '', 350000, 'Honda Brio 1.2 Satya E CVT tahun 2020 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1663 PJ', 'Honda Brio 1.2 E MT', 5, 2, 'City Car', 'MT', '2020', '', '', '', 300000, 'Honda Brio 1.2 E MT tahun 2020 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi manual dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1721 PI', 'Toyota Kijang Innova Reborn 2.0 G', 7, 2, 'MPV', 'AT', '2020', '', '', '', 550000, 'Toyota Kijang Innova Reborn 2.0 G tahun 2020 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1744 NZM', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2025', '', '', '', 750000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1752 AA', 'Honda Brio 1.2 E CVT', 5, 2, 'City Car', 'AT', '2020', '', '', '', 360000, 'Honda Brio 1.2 E CVT tahun 2020 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1755 AA', 'Honda Brio 1.2 Satya S CVT', 5, 2, 'City Car', 'AT', '2020', '', '', '', 340000, 'Honda Brio 1.2 Satya S CVT tahun 2020 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1759 PJ', 'Toyota Kijang Innova 2.0 V', 7, 2, 'MPV', 'AT', '2021', 'ft_depan_1782315293_6a3bf91d7762c.jpg', 'ft_blkg_1782315293_6a3bf91d77c96.jpg', 'ft_interior_1782315293_6a3bf91d77ff4.jpg', 600000, 'Toyota Kijang Innova 2.0 V A/T TGN tahun 2021 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1760 QF', 'Toyota Kijang Innova Reborn 2.0 V', 7, 2, 'MPV', 'AT', '2025', '', '', '', 650000, 'Toyota Kijang Innova Reborn 2.0 V tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1798 AB', 'Honda Brio 1.2 RS MT', 5, 2, 'City Car', 'MT', '2023', '', '', '', 350000, 'Honda Brio 1.2 RS MT tahun 2023 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi manual dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1847 PJ', 'Toyota Avanza Facelift', 7, 2, 'MPV', 'AT', '2021', 'ft_depan_1782637461_6a40e39544772.jpeg', 'ft_blkg_1782637461_6a40e39544aa2.jpg', 'ft_interior_1782637461_6a40e39544d76.jpg', 500000, 'Toyota Avanza Facelift merupakan kendaraan kategori MPV dengan kapasitas 7 penumpang yang dirancang untuk memberikan kenyamanan dan efisiensi dalam berbagai kebutuhan perjalanan. Kendaraan ini memiliki kabin yang luas, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1888 XY', 'Honda Brio 1.2 E MT', 5, 2, 'City Car', 'MT', '2022', '', '', '', 330000, 'Honda Brio 1.2 E MT tahun 2022 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi manual dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1895 PI', 'Toyota Kijang Innova Reborn 2.0 V', 7, 2, 'MPV', 'AT', '2020', '', '', '', 580000, 'Toyota Kijang Innova Reborn 2.0 V tahun 2020 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 1911 AB', 'Honda Brio Satya 1.2 E CVT CKD', 5, 2, 'City Car', 'AT', '2023', 'ft_depan_1782316518_6a3bfde609bdf.jpg', 'ft_blkg_1782316518_6a3bfde60a610.jpg', 'ft_interior_1782316518_6a3bfde60aaeb.jpg', 400000, 'Honda Brio Satya 1.2 E CVT CKD tahun 2023 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1926 AP', 'Honda Brio 1.2 RS CVT', 5, 2, 'City Car', 'AT', '2022', '', '', '', 380000, 'Honda Brio 1.2 RS CVT tahun 2022 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 1999 ZZ', 'Honda Brio 1.2 RS CVT', 5, 2, 'City Car', 'AT', '2024', '', '', '', 420000, 'Honda Brio 1.2 RS CVT tahun 2024 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 2000 AA', 'Toyota Kijang Innova Reborn 2.0 V', 7, 2, 'MPV', 'AT', '2022', '', '', '', 620000, 'Toyota Kijang Innova Reborn 2.0 V tahun 2022 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2111 BB', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2023', '', '', '', 720000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2023 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2222 CC', 'Honda Brio 1.2 Satya S MT', 5, 2, 'City Car', 'MT', '2021', '', '', '', 310000, 'Honda Brio 1.2 Satya S MT tahun 2021 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi manual dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 2285 BRH', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2026', '', '', '', 800000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2026 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2333 DD', 'Honda Brio 1.2 Satya E CVT', 5, 2, 'City Car', 'AT', '2025', '', '', '', 400000, 'Honda Brio 1.2 Satya E CVT tahun 2025 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 2444 EE', 'Toyota Kijang Innova Reborn 2.0 G', 7, 2, 'MPV', 'AT', '2024', '', '', '', 600000, 'Toyota Kijang Innova Reborn 2.0 G tahun 2024 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin bensin 2.0 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2555 FF', 'Honda Brio 1.2 RS CVT', 5, 2, 'City Car', 'AT', '2022', '', '', '', 380000, 'Honda Brio 1.2 RS CVT tahun 2022 merupakan kendaraan kategori City Car dengan kapasitas 5 penumpang yang dilengkapi transmisi otomatis CVT dan mesin bensin 1.2 liter. Kendaraan ini memiliki desain kompak, konsumsi bahan bakar yang efisien, serta cocok digunakan untuk kebutuhan transportasi harian maupun perjalanan dalam kota.', 'tersedia'),
('BN 2625 SQQ', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2025', '', '', '', 750000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2666 GG', 'Toyota Kijang Innova Reborn 2.4 G Diesel', 7, 2, 'MPV', 'AT', '2021', '', '', '', 600000, 'Toyota Kijang Innova Reborn 2.4 G Diesel tahun 2021 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2736 PKU', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2025', '', '', '', 750000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2025 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia'),
('BN 2786 VQT', 'Toyota Kijang Innova Reborn 2.4 V Diesel', 7, 2, 'MPV', 'AT', '2026', '', '', '', 800000, 'Toyota Kijang Innova Reborn 2.4 V Diesel tahun 2026 merupakan kendaraan kategori MPV berkapasitas 7 penumpang yang dilengkapi transmisi otomatis dan mesin diesel 2.4 liter. Kendaraan ini menawarkan kabin yang luas, kenyamanan berkendara yang baik, serta cocok digunakan untuk perjalanan keluarga, wisata, maupun perjalanan dinas.', 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `t_penyewa`
--

CREATE TABLE `t_penyewa` (
  `user_plg` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `sandi` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nik` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nm_lngkp` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `almt_lngkp` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `ft_ktp` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `ft_sim_a` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_penyewa`
--

INSERT INTO `t_penyewa` (`user_plg`, `sandi`, `nik`, `nm_lngkp`, `no_hp`, `almt_lngkp`, `ft_ktp`, `ft_sim_a`) VALUES
('andi', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM2', '1971030303030003', 'Andi Prasetyo', '082156789012', 'Jl. Merdeka RT 003 RW 003 Kel. Bukit Intan Kec. Bukit Intan Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('budi', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMN', '1971010101010001', 'Budi Santoso', '081234567890', 'Jl. Depati Hamzah RT 001 RW 001 Kel. Air Itam Kec. Bukit Intan Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('dewi', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM3', '1971040404040004', 'Dewi Lestari', '082267890123', 'Jl. Ahmad Yani RT 004 RW 004 Kel. Gabek Kec. Gabek Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('eko', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM4', '1971050505050005', 'Eko Widodo', '085378901234', 'Jl. Sudirman RT 005 RW 005 Kel. Girimaya Kec. Girimaya Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('Fadia', '$2y$10$3aMrY1RYR.a91tesY1/XreQKrVe/MR9Nf8aTTg..YAx0.BmIBVHvi', '1234567890123456', 'Fadia Khairun', '082245323221', 'Sungailiat, Desa Cungfo', NULL, NULL),
('gema', '$2y$10$yLYgFo9VhVu0L/g2pvvO5ujSpT0tO2dt28URyVD5MAVz8LYe2R9gq', '1971041210910012', 'Gema Saputra', '08126672888', 'Jl. Sriwijaya RT 004 RW 002 Kel. Asam Kec. Rangkui Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', 'ft_ktp_1782316776_6a3bfee8711f0.jpg', 'ft_sim_a_1782316776_6a3bfee871739.jpg'),
('irhamdani', '$2y$10$zc96yRwpTlsfvig7zA9nReftZAn1NFIi7o.WymyAfhIv1I8rVErc.', '1971012009940001', 'Irhamdani', '081378772655', 'Jl. Depati Hamzah RT 009 RW 003 Kel. Air Itam Kec. Bukit Intan Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', 'ft_ktp_1782632700_6a40d0fc4ee6c.webp', NULL),
('joko', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM8', '1971090909090009', 'Joko Susilo', '085812345678', 'Jl. Veteran RT 009 RW 009 Kel. Pasar Padi Kec. Taman Sari Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('Keshia', '$2y$10$vz0jYad/JhLBe7YrzvvjM.B9kxHwYwn/CVN0dyWEu2Wh01XtB4Leq', '1234567890123456', 'Keshia Minerva', '082279433221', 'Muntok, Kp Keranggan Atas', 'ft_ktp_20261026083023_bbe6c6728fa9d122.jpg', 'ft_sim_a_20261026083023_24a97d122e26293e.jpg'),
('lintang', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM9', '1971101010100010', 'Lintang Pradipta', '085923456789', 'Jl. Bangka RT 010 RW 010 Kel. Selindung Kec. Gerunggang Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('maya', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM7', '1971080808080008', 'Maya Sari', '085701234567', 'Jl. Imam Bonjol RT 008 RW 008 Kel. Opas Indah Kec. Gerunggang Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('Minerva', '$2y$10$33oo79eFUC0vlCBtPV7DWuLn51qzo7IbW4o5cVssBg1wokqVCtdG2', '0987654321098765', 'Keshia Minerva', '082245323221', 'Selindung', NULL, NULL),
('ratna', '$2y$10$NfvB6cLtk4ho9zvHGRw2VOQHiCvyhThcmzMkdgVUiTOU6qNO1b4du', '1012929292929292', 'Ratna Dewi', '08191919', 'Jl. Belinyu Bangka', NULL, NULL),
('rini', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM5', '1971060606060006', 'Rini Wulandari', '085489012345', 'Jl. Pahlawan RT 006 RW 006 Kel. Kampung Melayu Kec. Rangkui Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('siti', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM1', '1971020202020002', 'Siti Aminah', '081345678901', 'Jl. Sriwijaya RT 002 RW 002 Kel. Asam Kec. Rangkui Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('tono', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLM6', '1971070707070007', 'Tono Supriyadi', '085690123456', 'Jl. Diponegoro RT 007 RW 007 Kel. Ketapang Kec. Rangkui Kota Pangkalpinang Provinsi Kepulauan Bangka Belitung', NULL, NULL),
('zhel', '$2y$10$FefmNe96F5mOsr0wZc4Ka.4rGPr7EncOslCjNY5btOrcoX211tCpW', '8786858483828191', 'razhel', '0897654321', 'Jl kampung', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `t_pilih`
--

CREATE TABLE `t_pilih` (
  `kode_sewa` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `no_plat` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `hrg_sewa` mediumint NOT NULL,
  `n_hari` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_pilih`
--

INSERT INTO `t_pilih` (`kode_sewa`, `no_plat`, `hrg_sewa`, `n_hari`) VALUES
('INV/20260702/01', 'B 1583 RKE', 400000, 1),
('INV/20260702/01', 'BN 1460 AD', 500000, 1),
('INV/20260702/02', 'B 1173 HFF', 700000, 2),
('INV/20260703/01', 'BN 1194 AB', 500000, 1),
('INV/20260704/01', 'BN 1752 AA', 360000, 1),
('INV/20260704/02', 'BN 1194 AB', 500000, 1),
('INV/20260705/01', 'BN 1194 AB', 500000, 1),
('INV/20260705/02', 'BN 1194 AB', 500000, 1),
('INV/20260712/01', 'B 1583 RKE', 400000, 1),
('INV/20260712/02', 'B 4 LIP', 650000, 1),
('INV/20261031/01', 'BN 1000 OK', 800000, 1),
('INV/20261031/01', 'BN 1911 AB', 400000, 1),
('INV/20261031/02', 'BN 1000 OK', 800000, 1),
('INV/20261102/01', 'BN 1000 OK', 800000, 1),
('INV/20261102/02', 'BN 1000 OK', 800000, 1),
('INV/20261102/02', 'BN 1847 PJ', 500000, 1),
('INV/20261102/03', 'BN 1000 OK', 800000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `t_sewa`
--

CREATE TABLE `t_sewa` (
  `kode_sewa` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `wkt_ambil` time NOT NULL,
  `status_sewa` enum('diajukan','disetujui','ditolak','aktif','selesai','batal','keranjang') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT 'keranjang',
  `user_plg` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `t_sewa`
--

INSERT INTO `t_sewa` (`kode_sewa`, `tgl_mulai`, `tgl_selesai`, `wkt_ambil`, `status_sewa`, `user_plg`) VALUES
('INV/20260702/01', '2026-07-02', '2026-07-03', '14:07:00', 'selesai', 'gema'),
('INV/20260702/02', '2026-07-04', '2026-07-06', '21:15:00', 'selesai', 'irhamdani'),
('INV/20260703/01', '2026-07-03', '2026-07-04', '14:10:00', 'selesai', 'gema'),
('INV/20260704/01', '2026-07-04', '2026-07-05', '10:00:00', 'selesai', 'gema'),
('INV/20260704/02', '2026-07-04', '2026-07-05', '13:57:20', 'diajukan', 'ratna'),
('INV/20260705/01', '2026-07-05', '2026-07-06', '00:01:32', 'batal', 'aisyah'),
('INV/20260705/02', '2026-07-05', '2026-07-06', '00:57:48', 'disetujui', 'aisyah'),
('INV/20260712/01', '2026-07-12', '2026-07-13', '08:00:00', 'batal', 'aisyah'),
('INV/20260712/02', '2026-07-12', '2026-07-13', '08:00:00', 'batal', 'andi'),
('INV/20261102/01', '2026-11-02', '2026-11-03', '15:19:04', 'diajukan', 'Keshia'),
('INV/20261102/02', '2026-11-02', '2026-11-03', '15:37:45', 'keranjang', 'zhel'),
('INV/20261102/03', '2026-11-02', '2026-11-03', '15:53:04', 'diajukan', 'Keshia');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_admin`
--
ALTER TABLE `t_admin`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `t_bayar`
--
ALTER TABLE `t_bayar`
  ADD PRIMARY KEY (`kode_bayar`);

--
-- Indexes for table `t_denda`
--
ALTER TABLE `t_denda`
  ADD PRIMARY KEY (`kode_denda`);

--
-- Indexes for table `t_kembali`
--
ALTER TABLE `t_kembali`
  ADD PRIMARY KEY (`kode_kembali`);

--
-- Indexes for table `t_mobil`
--
ALTER TABLE `t_mobil`
  ADD PRIMARY KEY (`no_plat`);

--
-- Indexes for table `t_penyewa`
--
ALTER TABLE `t_penyewa`
  ADD PRIMARY KEY (`user_plg`);

--
-- Indexes for table `t_pilih`
--
ALTER TABLE `t_pilih`
  ADD PRIMARY KEY (`kode_sewa`,`no_plat`);

--
-- Indexes for table `t_sewa`
--
ALTER TABLE `t_sewa`
  ADD PRIMARY KEY (`kode_sewa`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

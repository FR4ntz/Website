-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 10:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_sita`
--

-- --------------------------------------------------------

--
-- Table structure for table `bimbingan`
--

CREATE TABLE `bimbingan` (
  `idBimbingan` char(13) NOT NULL,
  `Tanggal` datetime DEFAULT current_timestamp(),
  `Topik` varchar(100) DEFAULT NULL,
  `Bukti_Foto` varchar(255) DEFAULT NULL,
  `Status` enum('Menunggu','ACC','Revisi') DEFAULT 'Menunggu',
  `Catatan_Dosen` text DEFAULT NULL,
  `NIM` char(10) NOT NULL,
  `NIDN` char(10) NOT NULL,
  `idProposal` char(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bimbingan`
--

INSERT INTO `bimbingan` (`idBimbingan`, `Tanggal`, `Topik`, `Bukti_Foto`, `Status`, `Catatan_Dosen`, `NIM`, `NIDN`, `idProposal`) VALUES
('BIMB-001', '2025-12-29 10:00:00', 'Revisi Bab 1', 'foto1.jpg', 'ACC', 'Lanjut Bab 2', '2024081010', '041003', 'PROP-002');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `NIDN` char(10) NOT NULL,
  `Nama` varchar(128) NOT NULL,
  `Role` enum('Dosen','Koordinator','Penguji') DEFAULT 'Dosen',
  `Email` varchar(128) DEFAULT NULL,
  `Password` varchar(255) DEFAULT '202cb962ac59075b964b07152d234b70'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`NIDN`, `Nama`, `Role`, `Email`, `Password`) VALUES
('041002', 'Pak Chan', 'Penguji', NULL, '202cb962ac59075b964b07152d234b70'),
('041003', 'Pak Cahyono', 'Dosen', NULL, '202cb962ac59075b964b07152d234b70'),
('DOSEN001', 'Ibu Pembimbing', 'Dosen', 'dosen@upj.ac.id', '202cb962ac59075b964b07152d234b70'),
('KOOR001', 'Bpk. Koordinator', 'Koordinator', 'koor@upj.ac.id', '202cb962ac59075b964b07152d234b70');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `NIM` char(10) NOT NULL,
  `Nama` varchar(128) NOT NULL,
  `Total_SKS` tinyint(3) UNSIGNED DEFAULT 0,
  `Total_JSDP` smallint(5) UNSIGNED DEFAULT 0,
  `Email` varchar(128) DEFAULT NULL,
  `Password` varchar(255) DEFAULT '202cb962ac59075b964b07152d234b70'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`NIM`, `Nama`, `Total_SKS`, `Total_JSDP`, `Email`, `Password`) VALUES
('2024081010', 'Zidane Tirta Nugraha', 160, 1000, NULL, '202cb962ac59075b964b07152d234b70'),
('202408108', 'Laurensius Jovito', 160, 10000, NULL, '202cb962ac59075b964b07152d234b70');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notif` int(11) NOT NULL,
  `nim` char(10) DEFAULT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perpanjangan`
--

CREATE TABLE `perpanjangan` (
  `id_perpanjangan` int(11) NOT NULL,
  `nim` char(10) DEFAULT NULL,
  `id_proposal` char(13) DEFAULT NULL,
  `lama_perpanjangan` int(11) DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `status_perpanjangan` enum('Diajukan','Disetujui','Ditolak') DEFAULT 'Diajukan',
  `tanggal_pengajuan` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id_pesan` int(11) NOT NULL,
  `pengirim` char(10) DEFAULT NULL,
  `penerima` char(10) DEFAULT NULL,
  `isi_pesan` text DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id_pesan`, `pengirim`, `penerima`, `isi_pesan`, `waktu`, `is_read`) VALUES
(1, '202408108', 'DOSEN001', 'haloo bu', '2025-12-29 03:30:30', 0),
(2, 'DOSEN001', '202408108', 'halo', '2025-12-29 03:30:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `proposal`
--

CREATE TABLE `proposal` (
  `idProposal` char(13) NOT NULL,
  `Judul` text NOT NULL,
  `jenis_ta` enum('Skripsi','Tugas Akhir','Magang','Proyek','Rancang Bangun') NOT NULL DEFAULT 'Rancang Bangun',
  `status_pengajuan` enum('Diajukan','Disetujui','Ditolak','Revisi') DEFAULT 'Diajukan',
  `tanggal_pengajuan` date DEFAULT NULL,
  `catatan_koor` text DEFAULT NULL,
  `file_dokumen` varchar(255) DEFAULT NULL,
  `Total_Bimbingan` tinyint(4) DEFAULT 0,
  `NIM` char(10) NOT NULL,
  `NIDN_Pembimbing` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal`
--

INSERT INTO `proposal` (`idProposal`, `Judul`, `jenis_ta`, `status_pengajuan`, `tanggal_pengajuan`, `catatan_koor`, `file_dokumen`, `Total_Bimbingan`, `NIM`, `NIDN_Pembimbing`) VALUES
('PROP-002', 'Proposal Saya', 'Rancang Bangun', 'Disetujui', '2025-12-28', 'Semangat', '2024081010_1766946505.pdf', 0, '2024081010', '041003');

-- --------------------------------------------------------

--
-- Table structure for table `sidang`
--

CREATE TABLE `sidang` (
  `idSidang` char(12) NOT NULL,
  `Ruangan` varchar(50) DEFAULT NULL,
  `status_sidang` enum('Menunggu Jadwal','Dijadwalkan','Selesai','Lulus','Tidak Lulus','Revisi') DEFAULT 'Menunggu Jadwal',
  `nilai_akhir` float DEFAULT NULL,
  `tanggal_sidang` datetime DEFAULT NULL,
  `file_laporan` varchar(255) DEFAULT NULL,
  `idProposal` char(13) NOT NULL,
  `NIM` char(10) NOT NULL,
  `NIDN` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bimbingan`
--
ALTER TABLE `bimbingan`
  ADD PRIMARY KEY (`idBimbingan`),
  ADD KEY `fk_bimb_mhs` (`NIM`),
  ADD KEY `fk_bimb_dosen` (`NIDN`),
  ADD KEY `fk_bimb_prop` (`idProposal`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`NIDN`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`NIM`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notif`);

--
-- Indexes for table `perpanjangan`
--
ALTER TABLE `perpanjangan`
  ADD PRIMARY KEY (`id_perpanjangan`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id_pesan`);

--
-- Indexes for table `proposal`
--
ALTER TABLE `proposal`
  ADD PRIMARY KEY (`idProposal`),
  ADD UNIQUE KEY `unique_mhs_prop` (`NIM`),
  ADD KEY `fk_prop_dosen` (`NIDN_Pembimbing`);

--
-- Indexes for table `sidang`
--
ALTER TABLE `sidang`
  ADD PRIMARY KEY (`idSidang`),
  ADD KEY `fk_sid_prop` (`idProposal`),
  ADD KEY `fk_sid_mhs` (`NIM`),
  ADD KEY `fk_sid_dosen` (`NIDN`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perpanjangan`
--
ALTER TABLE `perpanjangan`
  MODIFY `id_perpanjangan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id_pesan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bimbingan`
--
ALTER TABLE `bimbingan`
  ADD CONSTRAINT `fk_bimb_dosen` FOREIGN KEY (`NIDN`) REFERENCES `dosen` (`NIDN`),
  ADD CONSTRAINT `fk_bimb_mhs` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bimb_prop` FOREIGN KEY (`idProposal`) REFERENCES `proposal` (`idProposal`) ON DELETE CASCADE;

--
-- Constraints for table `proposal`
--
ALTER TABLE `proposal`
  ADD CONSTRAINT `fk_prop_dosen` FOREIGN KEY (`NIDN_Pembimbing`) REFERENCES `dosen` (`NIDN`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prop_mhs` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE;

--
-- Constraints for table `sidang`
--
ALTER TABLE `sidang`
  ADD CONSTRAINT `fk_sid_dosen` FOREIGN KEY (`NIDN`) REFERENCES `dosen` (`NIDN`),
  ADD CONSTRAINT `fk_sid_mhs` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sid_prop` FOREIGN KEY (`idProposal`) REFERENCES `proposal` (`idProposal`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

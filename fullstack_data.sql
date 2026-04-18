-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Jan 2026 pada 19.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fullstack`
--
CREATE DATABASE IF NOT EXISTS fullstack;

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun`
--

CREATE TABLE fullstack.`akun` (
  `username` varchar(20) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `nrp_mahasiswa` char(9) DEFAULT NULL,
  `npk_dosen` char(6) DEFAULT NULL,
  `isadmin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `akun`
--

INSERT INTO fullstack.`akun` (`username`, `password`, `nrp_mahasiswa`, `npk_dosen`, `isadmin`) VALUES
('admin', '$2y$10$jO43WFjGtlzw0xA.BV08uO8m.XkfbjlVyO.7Kt6UMyoXArHLmC92i', NULL, NULL, 1),
('D1', '$2y$10$41VW2zqW.U86Qu2GJQYXxONIn.1ZV0TVFex18..9.cq6QlaTllF16', NULL, 'D1', 0),
('D2', '$2y$10$gw2XjVhKXOhalIQ4YdjGEO2OBbt/SkcE1r18WXA5f5s5NEW77xvp2', NULL, 'D2', 0),
('D3', '$2y$10$jNtQe13ckBnpIJq1DNrRZuEmmszxW40m.YDDfs1pO3bHO4uUwl2hu', NULL, 'D3', 0),
('D4', '$2y$10$JOtCT/Ncy6rA199tu8uZ7uW5JDjwgIF3dkoFFF1iujuGKtekCwi5q', NULL, 'D4', 0),
('D5', '$2y$10$TZoHCdMDIYOORL3uQ1FHU.Oe3OErsXV04iKpCcA0GT98Al5zEIyAu', NULL, 'D5', 0),
('D6', '$2y$10$5HQDuOUAP9dCacA/PMEjNelPIHeWddMdZh3PqsXLOOIbUJ7s4vC3K', NULL, 'D6', 0),
('M1', '$2y$10$g27qChRDkvS3gewhZ7.9H.E80FYqBW8YZ5DqTlirqzZvRH8FGRWFi', 'M1', NULL, 0),
('M2', '$2y$10$kwXyEBMMGX34jfNptIF7i.7yKCm5/F8mX.sweVmmhvwC1GtAbAGYa', 'M2', NULL, 0),
('M3', '$2y$10$Sw1WNyht390b6baG7B3I5OamOl1ysMj1CObt8wNY/mipwqbHcmUKO', 'M3', NULL, 0),
('M4', '$2y$10$7YE0mykJSVnsRYOcsdziEeYJ2auYvybu.l1nXR3sfXgveTSy/G41i', 'M4', NULL, 0),
('M5', '$2y$10$zWv67Nowxlao.4cmpUn4wOtdRm/PliumQfnbZRBuEIjre.qk89CxO', 'M5', NULL, 0),
('M6', '$2y$10$OHiD3E8o05uLIXDRyo7yM.b0XZMceoBdrT7luDcPcIP81k6GXHGB.', 'M6', NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

CREATE TABLE fullstack.`chat` (
  `idchat` int(11) NOT NULL,
  `idthread` int(11) NOT NULL,
  `username_pembuat` varchar(20) NOT NULL,
  `isi` text DEFAULT NULL,
  `tanggal_pembuatan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `chat`
--

INSERT INTO fullstack.`chat` (`idchat`, `idthread`, `username_pembuat`, `isi`, `tanggal_pembuatan`) VALUES
(8, 8, 'D1', 'Halo Semuanya Selamat bergabung!!!', '2026-01-14 01:14:57'),
(9, 8, 'M1', 'HI', '2026-01-14 01:15:07'),
(10, 7, 'M1', 'Halo Semuanya Selamat bergabung!!!', '2026-01-14 01:15:17'),
(11, 7, 'D1', 'hi', '2026-01-14 01:15:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dosen`
--

CREATE TABLE fullstack.`dosen` (
  `npk` char(6) NOT NULL,
  `nama` varchar(45) DEFAULT NULL,
  `foto_extension` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `dosen`
--

INSERT INTO fullstack.`dosen` (`npk`, `nama`, `foto_extension`) VALUES
('D1', 'D1', 'png'),
('D2', 'D2', 'png'),
('D3', 'D3', 'png'),
('D4', 'D4', 'png'),
('D5', 'D5', 'png'),
('D6', 'D6', 'png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `event`
--

CREATE TABLE fullstack.`event` (
  `idevent` int(11) NOT NULL,
  `idgrup` int(11) NOT NULL,
  `judul` varchar(45) DEFAULT NULL,
  `judul-slug` varchar(45) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `jenis` enum('Privat','Publik') DEFAULT NULL,
  `poster_extension` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `event`
--

INSERT INTO fullstack.`event` (`idevent`, `idgrup`, `judul`, `judul-slug`, `tanggal`, `keterangan`, `jenis`, `poster_extension`) VALUES
(23, 71, 'QUIZ', NULL, '2026-01-14 01:12:00', 'QUIZ', NULL, NULL),
(24, 70, 'UTS', NULL, '2026-01-14 01:13:00', 'UTS', NULL, NULL),
(25, 67, 'QUIZ', NULL, '2026-01-14 01:13:00', 'QUIZ', NULL, NULL),
(26, 66, 'UTS', NULL, '2026-01-14 01:13:00', 'UTS', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `grup`
--

CREATE TABLE fullstack.`grup` (
  `idgrup` int(11) NOT NULL,
  `username_pembuat` varchar(20) NOT NULL,
  `nama` varchar(45) DEFAULT NULL,
  `deskripsi` varchar(45) DEFAULT NULL,
  `tanggal_pembentukan` datetime DEFAULT NULL,
  `jenis` enum('Privat','Publik') DEFAULT NULL,
  `kode_pendaftaran` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `grup`
--

INSERT INTO fullstack.`grup` (`idgrup`, `username_pembuat`, `nama`, `deskripsi`, `tanggal_pembentukan`, `jenis`, `kode_pendaftaran`) VALUES
(32, 'D4', 'Private Group D4', 'Grup private dosen D4', '2026-01-14 01:05:16', 'Publik', 'PRD4'),
(33, 'D4', 'Public Group D4', 'Grup public dosen D4', '2026-01-14 01:05:16', 'Privat', 'PUD4'),
(34, 'D5', 'Private Group D5', 'Grup private dosen D5', '2026-01-14 01:05:16', 'Privat', 'PRD5'),
(35, 'D5', 'Public Group D5', 'Grup public dosen D5', '2026-01-14 01:05:16', 'Privat', 'PUD5'),
(42, 'D4', 'Private Group D4', 'Grup private dosen D4', '2026-01-14 01:05:19', 'Publik', 'PRD4'),
(43, 'D4', 'Public Group D4', 'Grup public dosen D4', '2026-01-14 01:05:19', 'Privat', 'PUD4'),
(44, 'D5', 'Private Group D5', 'Grup private dosen D5', '2026-01-14 01:05:19', 'Publik', 'PRD5'),
(45, 'D5', 'Public Group D5', 'Grup public dosen D5', '2026-01-14 01:05:19', 'Privat', 'PUD5'),
(52, 'D4', 'Private Group D4', 'Grup private dosen D4', '2026-01-14 01:05:20', 'Publik', 'PRD4'),
(53, 'D4', 'Public Group D4', 'Grup public dosen D4', '2026-01-14 01:05:20', 'Privat', 'PUD4'),
(54, 'D5', 'Private Group D5', 'Grup private dosen D5', '2026-01-14 01:05:20', 'Publik', 'PRD5'),
(55, 'D5', 'Public Group D5', 'Grup public dosen D5', '2026-01-14 01:05:20', 'Privat', 'PUD5'),
(62, 'D4', 'Private Group D4', 'Grup private dosen D4', '2026-01-14 01:05:27', 'Publik', 'PRD4'),
(63, 'D4', 'Public Group D4', 'Grup public dosen D4', '2026-01-14 01:05:27', 'Publik', 'PUD4'),
(64, 'D5', 'Private Group D5', 'Grup private dosen D5', '2026-01-14 01:05:27', 'Publik', 'PRD5'),
(65, 'D5', 'Public Group D5', 'Grup public dosen D5', '2026-01-14 01:05:27', 'Publik', 'PUD5'),
(66, 'D1', 'Hybrid Mobile Programing', 'Hybrid Mobile Programing', '2026-01-14 01:11:05', 'Publik', 'FF4DB3'),
(67, 'D1', 'Hybrid Mobile Programing Private', 'Hybrid Mobile Programing Private', '2026-01-14 01:11:28', 'Privat', 'C0D1E3'),
(68, 'D2', 'Native Mobile Programing', 'Native Mobile Programing', '2026-01-14 01:11:55', 'Publik', '6F5F7A'),
(69, 'D2', 'Native Mobile Programing PRIVATE', 'Native Mobile Programing', '2026-01-14 01:12:07', 'Privat', '9F0207'),
(70, 'D3', 'FullStack Programing', 'FullStack Programing', '2026-01-14 01:12:26', 'Publik', '8A30DD'),
(71, 'D3', 'FullStack Programing PRIVATE', 'FullStack Programing', '2026-01-14 01:12:44', 'Privat', 'D49484');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE fullstack.`mahasiswa` (
  `nrp` char(9) NOT NULL,
  `nama` varchar(45) DEFAULT NULL,
  `gender` enum('Pria','Wanita') DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `angkatan` year(4) DEFAULT NULL,
  `foto_extention` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO fullstack.`mahasiswa` (`nrp`, `nama`, `gender`, `tanggal_lahir`, `angkatan`, `foto_extention`) VALUES
('M1', 'M1', 'Pria', '2025-10-02', '1901', 'png'),
('M2', 'M2', 'Pria', '2025-10-02', '1901', 'png'),
('M3', 'M3', 'Wanita', '2025-11-05', '1901', 'png'),
('M4', 'M4', 'Wanita', '2025-10-09', '2020', 'png'),
('M5', 'M5', 'Pria', '2025-10-07', '2010', 'png'),
('M6', 'M6', 'Pria', '2025-10-02', '2010', 'png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_grup`
--

CREATE TABLE fullstack.`member_grup` (
  `idgrup` int(11) NOT NULL,
  `username` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `member_grup`
--

INSERT INTO fullstack.`member_grup` (`idgrup`, `username`) VALUES
(66, 'D1'),
(67, 'D1'),
(67, 'M1'),
(68, 'D2'),
(69, 'D2'),
(70, 'D3'),
(71, 'D3');

-- --------------------------------------------------------

--
-- Struktur dari tabel `thread`
--

CREATE TABLE fullstack.`thread` (
  `idthread` int(11) NOT NULL,
  `username_pembuat` varchar(20) NOT NULL,
  `idgrup` int(11) NOT NULL,
  `tanggal_pembuatan` datetime DEFAULT NULL,
  `status` enum('Open','Close') DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `thread`
--

INSERT INTO fullstack.`thread` (`idthread`, `username_pembuat`, `idgrup`, `tanggal_pembuatan`, `status`) VALUES
(7, 'M1', 67, '2026-01-14 01:14:40', 'Open'),
(8, 'D1', 67, '2026-01-14 01:14:41', 'Open'),
(9, 'D1', 67, '2026-01-14 01:15:36', 'Close'),
(10, 'M1', 67, '2026-01-14 01:15:39', 'Close');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `akun`
--
ALTER TABLE fullstack.`akun`
  ADD PRIMARY KEY (`username`),
  ADD KEY `fk_akun_mahasiswa_idx` (`nrp_mahasiswa`),
  ADD KEY `fk_akun_dosen1_idx` (`npk_dosen`);

--
-- Indeks untuk tabel `chat`
--
ALTER TABLE fullstack.`chat`
  ADD PRIMARY KEY (`idchat`),
  ADD KEY `fk_chat_thread1_idx` (`idthread`),
  ADD KEY `fk_chat_akun1_idx` (`username_pembuat`);

--
-- Indeks untuk tabel `dosen`
--
ALTER TABLE fullstack.`dosen`
  ADD PRIMARY KEY (`npk`);

--
-- Indeks untuk tabel `event`
--
ALTER TABLE fullstack.`event`
  ADD PRIMARY KEY (`idevent`),
  ADD KEY `fk_event_grup1_idx` (`idgrup`);

--
-- Indeks untuk tabel `grup`
--
ALTER TABLE fullstack.`grup`
  ADD PRIMARY KEY (`idgrup`),
  ADD KEY `fk_grup_akun1_idx` (`username_pembuat`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE fullstack.`mahasiswa`
  ADD PRIMARY KEY (`nrp`);

--
-- Indeks untuk tabel `member_grup`
--
ALTER TABLE fullstack.`member_grup`
  ADD PRIMARY KEY (`idgrup`,`username`),
  ADD KEY `fk_grup_has_akun_akun1_idx` (`username`),
  ADD KEY `fk_grup_has_akun_grup1_idx` (`idgrup`);

--
-- Indeks untuk tabel `thread`
--
ALTER TABLE fullstack.`thread`
  ADD PRIMARY KEY (`idthread`),
  ADD KEY `fk_thread_akun1_idx` (`username_pembuat`),
  ADD KEY `fk_thread_grup1_idx` (`idgrup`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `chat`
--
ALTER TABLE fullstack.`chat`
  MODIFY `idchat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `event`
--
ALTER TABLE fullstack.`event`
  MODIFY `idevent` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `grup`
--
ALTER TABLE fullstack.`grup`
  MODIFY `idgrup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT untuk tabel `thread`
--
ALTER TABLE fullstack.`thread`
  MODIFY `idthread` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `akun`
--
ALTER TABLE fullstack.`akun`
  ADD CONSTRAINT `fk_akun_dosen1` FOREIGN KEY (`npk_dosen`) REFERENCES `dosen` (`npk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_akun_mahasiswa` FOREIGN KEY (`nrp_mahasiswa`) REFERENCES `mahasiswa` (`nrp`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `chat`
--
ALTER TABLE fullstack.`chat`
  ADD CONSTRAINT `fk_chat_akun1` FOREIGN KEY (`username_pembuat`) REFERENCES `akun` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_thread1` FOREIGN KEY (`idthread`) REFERENCES `thread` (`idthread`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `event`
--
ALTER TABLE fullstack.`event`
  ADD CONSTRAINT `fk_event_grup1` FOREIGN KEY (`idgrup`) REFERENCES `grup` (`idgrup`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `grup`
--
ALTER TABLE fullstack.`grup`
  ADD CONSTRAINT `fk_grup_akun1` FOREIGN KEY (`username_pembuat`) REFERENCES `akun` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `member_grup`
--
ALTER TABLE fullstack.`member_grup`
  ADD CONSTRAINT `fk_grup_has_akun_akun1` FOREIGN KEY (`username`) REFERENCES `akun` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_grup_has_akun_grup1` FOREIGN KEY (`idgrup`) REFERENCES `grup` (`idgrup`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `thread`
--
ALTER TABLE fullstack.`thread`
  ADD CONSTRAINT `fk_thread_akun1` FOREIGN KEY (`username_pembuat`) REFERENCES `akun` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_thread_grup1` FOREIGN KEY (`idgrup`) REFERENCES `grup` (`idgrup`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


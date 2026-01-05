-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 05 Jan 2026 pada 23.31
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
-- Database: `rt05_portal`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `created_at`) VALUES
(1, 'admin', '$2y$10$aVBwe3vVGvcuSNCjN5xYO.EUnsp.KBlf0UQiEadNJeyQh3DVon4HS', 'Admin RT005', '2025-11-06 11:09:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `title`, `content`, `status`, `created_at`) VALUES
(4, 4, 'gotong royong', 'jalan rt 005', 'pending', '2025-11-11 04:38:50'),
(5, 5, 'jalan rusak', 'di depan rumah bapak ahmad jalannya rusak jika hujan membuat becek dan bisa mengakibatkan jatuh.', 'resolved', '2025-11-11 06:00:12'),
(6, 5, 'lampu jalan mati', 'lampu jalan di depan ada yang mati', 'resolved', '2025-11-20 03:02:44'),
(7, 7, 'waspada orang asing', 'ada orang mondar mandir di depan rumah nomer 6', 'in_progress', '2025-11-20 06:44:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `complaint_messages`
--

CREATE TABLE `complaint_messages` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `sender_role` varchar(20) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `message_text` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `complaint_messages`
--

INSERT INTO `complaint_messages` (`id`, `complaint_id`, `sender_role`, `sender_id`, `sender_name`, `message_text`, `created_at`) VALUES
(3, 6, 'admin', 0, 'Admin RT005', 'xbhxhdhxbhd', '2026-01-06 04:45:03'),
(4, 7, 'admin', 1, 'Admin RT005', 'halo kami akan memprosesnya yaa ditunggu', '2026-01-06 05:26:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `complaint_responses`
--

CREATE TABLE `complaint_responses` (
  `complaint_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `responded_by` varchar(100) NOT NULL,
  `responded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `complaint_responses`
--

INSERT INTO `complaint_responses` (`complaint_id`, `response_text`, `responded_by`, `responded_at`) VALUES
(6, 'xbhxhdhxbhd', 'Admin RT005', '2026-01-06 04:45:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `complaint_views`
--

CREATE TABLE `complaint_views` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `complaint_views`
--

INSERT INTO `complaint_views` (`id`, `complaint_id`, `user_id`, `viewed_at`) VALUES
(1, 7, 9, '2026-01-06 04:43:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `topic_id`, `user_id`, `content`, `created_at`) VALUES
(9, 5, 4, 'setuju', '2025-11-20 02:48:41'),
(10, 6, 4, 'mau di adakan lomba?', '2025-11-20 06:50:44'),
(11, 6, 7, 'boleh', '2025-11-20 06:52:41'),
(12, 6, 9, 'iya knapa', '2026-01-05 21:42:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `forum_topics`
--

CREATE TABLE `forum_topics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `forum_topics`
--

INSERT INTO `forum_topics` (`id`, `user_id`, `title`, `created_at`) VALUES
(5, 5, 'Bagaimana jika setiap hari minggu di adakan bersih-bersih bersama', '2025-11-19 15:02:21'),
(6, 5, 'perayaan 17 agustus', '2025-11-20 06:49:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `letters`
--

CREATE TABLE `letters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','review','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `letters`
--

INSERT INTO `letters` (`id`, `user_id`, `type`, `subject`, `details`, `file_path`, `status`, `created_at`) VALUES
(4, 4, 'surat_pengantar', 'kartu keluarga', 'warga muara telang', NULL, 'approved', '2025-11-11 04:41:26'),
(5, 7, 'surat_domisili', 'warga pindahan', 'warga baru harus mengurus surat domisili', NULL, 'review', '2025-11-20 06:57:57'),
(6, 9, 'surat_domisili', 'membuat surat ingin pindah kota', 'djbddbhde', NULL, 'approved', '2026-01-05 22:11:16'),
(7, 9, 'surat_domisili', 'membuat surat ingin pindah kota', 'djbddbhde', NULL, 'approved', '2026-01-05 22:11:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `letter_attachments`
--

CREATE TABLE `letter_attachments` (
  `id` int(11) NOT NULL,
  `letter_id` int(11) NOT NULL,
  `label` varchar(20) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `letter_attachments`
--

INSERT INTO `letter_attachments` (`id`, `letter_id`, `label`, `file_path`, `created_at`) VALUES
(1, 6, 'kk', 'uploads/L_9_1767651076_kk.png', '2026-01-06 05:11:16'),
(2, 6, 'ktp', 'uploads/L_9_1767651076_ktp.png', '2026-01-06 05:11:16'),
(3, 7, 'kk', 'uploads/L_9_1767651113_kk.png', '2026-01-06 05:11:53'),
(4, 7, 'ktp', 'uploads/L_9_1767651113_ktp.png', '2026-01-06 05:11:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `letter_notifications`
--

CREATE TABLE `letter_notifications` (
  `id` int(11) NOT NULL,
  `letter_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `letter_notifications`
--

INSERT INTO `letter_notifications` (`id`, `letter_id`, `message`, `created_at`, `created_by`) VALUES
(1, 5, 'Silakan ambil surat di rumah RT.', '2026-01-06 05:02:13', 'Admin RT005'),
(2, 6, 'Silakan ambil surat di rumah RT.', '2026-01-06 05:11:28', 'Admin RT005'),
(3, 7, 'surat ini uda selesai silahkan diambil di rumah rt', '2026-01-06 05:14:56', 'Admin RT005');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `rt` tinyint(4) NOT NULL DEFAULT 5,
  `rw` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nik`, `name`, `email`, `avatar_path`, `phone`, `password`, `address`, `created_at`, `status`, `rt`, `rw`) VALUES
(4, '1234567891123413', 'mellyana rafizah', 'mely123@gmail.com', NULL, NULL, '$2y$10$p7s6cT3ttwN4u4TcELeNxOvOs6tV.zlRwwCSlxIqPIZdAZ1PM4FXO', 'muara telang', '2025-11-11 04:07:35', 'active', 5, 3),
(5, '1143999827665980', 'fiki nurita', 'fikinur09@gmail.com', NULL, NULL, '$2y$10$Vn7hBnOqh.8374rUAIVz4ODcmhYVnHvk.Gd0j1KnDat46t3RkKMdK', '', '2025-11-11 05:56:26', 'active', 5, 3),
(6, '1234135663471115', 'riski alamsyah', 'riski1303', NULL, NULL, '$2y$10$y3Vj4gBXh36c3E0/H7CizOmST.oOuaEPrJqjEqLYEIQf9MFIbPhVW', 'muara telang', '2025-11-14 09:12:29', 'active', 5, 3),
(7, '123456789101667890', 'mellyana rafizah', 'melyaja123', NULL, NULL, '$2y$10$ziWbCLV2PM31kLooXWYHJupxn7xJF0SM2vfNall6ixeLaiTMsKbgG', 'muara telang', '2025-11-20 06:34:17', 'active', 5, 3),
(8, '123456789', 'Muhammad Rzki Pratama', 'rizki.qq05@gmail.com', NULL, NULL, '$2y$10$1RVA0vV901gk7juk50qJqOTQJsowWO0R2Og1FveZAwAzJbKjAp8xy', 'JL Sakura no 2 kec tanjung senang kel way kandis', '2026-01-05 13:49:21', 'active', 5, 1),
(9, '1234567890987654', 'Muhammad Rzki Pratama', 'kiki05@gmail.com', NULL, NULL, '$2y$10$EYXKd9XdwJqQDStIwQ63bOBHdYn7xydvt0yqmCqECjpwJUFBW8L.G', 'JL Sakura no 2 kec tanjung senang kel way kandis', '2026-01-05 13:55:21', 'active', 5, 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `complaint_messages`
--
ALTER TABLE `complaint_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`);

--
-- Indeks untuk tabel `complaint_responses`
--
ALTER TABLE `complaint_responses`
  ADD PRIMARY KEY (`complaint_id`);

--
-- Indeks untuk tabel `complaint_views`
--
ALTER TABLE `complaint_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_view` (`complaint_id`,`user_id`),
  ADD KEY `idx_complaint` (`complaint_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indeks untuk tabel `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `letters`
--
ALTER TABLE `letters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `letter_attachments`
--
ALTER TABLE `letter_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `letter_id` (`letter_id`);

--
-- Indeks untuk tabel `letter_notifications`
--
ALTER TABLE `letter_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `letter_id` (`letter_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `complaint_messages`
--
ALTER TABLE `complaint_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `complaint_views`
--
ALTER TABLE `complaint_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `letters`
--
ALTER TABLE `letters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `letter_attachments`
--
ALTER TABLE `letter_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `letter_notifications`
--
ALTER TABLE `letter_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `complaint_views`
--
ALTER TABLE `complaint_views`
  ADD CONSTRAINT `fk_cv_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `letters`
--
ALTER TABLE `letters`
  ADD CONSTRAINT `letters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

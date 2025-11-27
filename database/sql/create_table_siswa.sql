-- Query CREATE TABLE untuk tabel siswa_1
-- Eksekusi query ini di database Anda

CREATE TABLE `siswa_1` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nis` VARCHAR(20) NOT NULL,
  `nisn` VARCHAR(20) NOT NULL,
  `nama` VARCHAR(255) NOT NULL,
  `jk` ENUM('l', 'p') NOT NULL COMMENT 'Jenis Kelamin: l=Laki-laki, p=Perempuan',
  `tempat_lahir` VARCHAR(100) NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `alamat` TEXT NOT NULL,
  `kelas_id` INT UNSIGNED NOT NULL,
  `jurusan_id` INT UNSIGNED NOT NULL,
  `orangtua_id` INT UNSIGNED NOT NULL,
  `no_hp` VARCHAR(20) NULL,
  `status` ENUM('aktif', 'alumni', 'keluar') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Indexes
  KEY `idx_nis` (`nis`),
  KEY `idx_nisn` (`nisn`),
  KEY `idx_nama` (`nama`),
  KEY `idx_kelas_id` (`kelas_id`),
  KEY `idx_jurusan_id` (`jurusan_id`),
  KEY `idx_orangtua_id` (`orangtua_id`),
  KEY `idx_status` (`status`)

  -- Uncomment baris di bawah ini jika tabel kelas, jurusan, dan orangtua sudah ada
  -- CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  -- CONSTRAINT `fk_siswa_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  -- CONSTRAINT `fk_siswa_orangtua` FOREIGN KEY (`orangtua_id`) REFERENCES `orangtua` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data siswa';


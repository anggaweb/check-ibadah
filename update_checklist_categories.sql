-- Tambahkan kategori baru untuk checklist_template
INSERT INTO checklist_template (category, item_name, is_parent, parent_id)
VALUES 
-- Ruang Penyambutan Lantai Dasar
('Ruang Penyambutan Lantai Dasar', 'Meja penyambutan bersih dan rapi', 0, NULL),
('Ruang Penyambutan Lantai Dasar', 'Amplop Persembahan sudah dipersiapkan (perpuluhan / diakon)', 0, NULL),
('Ruang Penyambutan Lantai Dasar', 'Petugas ushers sudah stand-by', 0, NULL),
('Ruang Penyambutan Lantai Dasar', 'Apakah seluruh area sudah bersih (lantai 1 dan 2)', 0, NULL),
('Ruang Penyambutan Lantai Dasar', 'Nyalakan AC sesuai kebutuhan (lantai 1 & 2)', 0, NULL),

-- Ruang Ibadah
('Ruang Ibadah', 'TV berfungsi dengan baik', 0, NULL),
('Ruang Ibadah', 'Semua speaker monitor berfungsi', 0, NULL),
('Ruang Ibadah', 'Microfon hidup dan baterai cukup', 0, NULL),

-- Streaming Setup
('Streaming Setup', 'Pastikan Kamera Streaming dengan baterai full', 0, NULL),
('Streaming Setup', 'Pastikan Komputer Streaming menyala', 0, NULL),
('Streaming Setup', 'Software streaming sudah siap dijalankan', 0, NULL),
('Streaming Setup', 'Link streaming diuji 15 menit sebelum ibadah mulai', 0, NULL),
('Streaming Setup', 'File Video Warta Jemaat sudah tersedia di Komputer Streaming', 0, NULL),

-- Sound System
('Sound System', 'Semua alat musik siap digunakan', 1, NULL),
('Sound System', 'checksound Keyboard', 0, LAST_INSERT_ID()),
('Sound System', 'checksound Gitar Listrik', 0, LAST_INSERT_ID()-1),
('Sound System', 'checksound Bass', 0, LAST_INSERT_ID()-2),
('Sound System', 'checksound Drum', 0, LAST_INSERT_ID()-3),
('Sound System', 'Soundman sudah hadir', 0, NULL),

-- Multimedia
('Multimedia', 'Pastikan Easy Worship script sudah ter-update', 1, NULL),
('Multimedia', 'sudah berisi lagu-lagu dari WL', 0, LAST_INSERT_ID()),
('Multimedia', 'sudah ada Bacaan Bertanggapan', 0, LAST_INSERT_ID()-1),
('Multimedia', 'sudah ada Doa Syafaat', 0, LAST_INSERT_ID()-2),
('Multimedia', 'konfirmasi urutan ibadah ke WL', 0, LAST_INSERT_ID()-3),
('Multimedia', 'sudah ada Bahan Khotbah', 0, LAST_INSERT_ID()-4),
('Multimedia', 'File Video Warta Jemaat sudah tersedia di Laptop Multimedia', 0, NULL),

-- Petugas
('Petugas', 'Pengkhotbah sudah hadir', 0, NULL),
('Petugas', 'WL sudah hadir', 0, NULL),
('Petugas', 'Singer sudah hadir', 0, NULL),
('Petugas', 'Pemusik sudah hadir', 0, NULL),
('Petugas', 'Streaming sudah hadir', 0, NULL),
('Petugas', 'Multimedia sudah hadir', 0, NULL),
('Petugas', 'Soundman sudah hadir', 0, NULL);

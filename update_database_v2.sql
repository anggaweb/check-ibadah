ALTER TABLE users ADD COLUMN email VARCHAR(255) AFTER username;

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    review TEXT NOT NULL,
    rating INT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Drop existing checklist_items table to recreate with new structure
DROP TABLE IF EXISTS checklist_items;

-- Create new checklist_items table with updated structure
CREATE TABLE checklist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    item_text TEXT NOT NULL,
    parent_id INT DEFAULT NULL,
    sort_order INT NOT NULL,
    FOREIGN KEY (parent_id) REFERENCES checklist_items(id) ON DELETE CASCADE
);

-- Insert the new checklist structure
INSERT INTO checklist_items (category, item_text, parent_id, sort_order) VALUES
-- H-2 s/d H-1 Sebelum Ibadah
('H-2 s/d H-1 Sebelum Ibadah', 'Anggota Tim Pelayan sudah fixed ?', NULL, 1),
('H-2 s/d H-1 Sebelum Ibadah', 'Check Alat musik saat latihan.', NULL, 2),
('H-2 s/d H-1 Sebelum Ibadah', 'checksound Keyboard.', NULL, 3),
('H-2 s/d H-1 Sebelum Ibadah', 'checksound Gitar Listrik.', NULL, 4),
('H-2 s/d H-1 Sebelum Ibadah', 'checksound Bass.', NULL, 5),
('H-2 s/d H-1 Sebelum Ibadah', 'checksound Drum.', NULL, 6),
('H-2 s/d H-1 Sebelum Ibadah', 'Konsumsi Tersedia ?', NULL, 7),
('H-2 s/d H-1 Sebelum Ibadah', 'Ada Perjamuan Kudus ?', NULL, 8),
('H-2 s/d H-1 Sebelum Ibadah', 'Anggur dan roti sudah disiapkan.', NULL, 9),
('H-2 s/d H-1 Sebelum Ibadah', 'Petugas Perjamuan Kudus Sudah Fix ?', NULL, 10),
('H-2 s/d H-1 Sebelum Ibadah', 'Konfirmasi tema ibadah dan materi presentasi ke tim multimedia.', NULL, 11),
('H-2 s/d H-1 Sebelum Ibadah', 'Pembuatan Thumbnail untuk Streaming.', NULL, 12),
('H-2 s/d H-1 Sebelum Ibadah', 'Microfon hidup dan baterai cukup.', NULL, 13),
('H-2 s/d H-1 Sebelum Ibadah', 'Pembuatan Video Warta Jemaat.', NULL, 14),

-- Hari-H Sebelum Ibadah
('Hari-H Sebelum Ibadah', 'Meja penyambutan bersih dan rapi.', NULL, 15),
('Hari-H Sebelum Ibadah', 'Amplop Persembahan sudah dipersiapkan (perpuluhan / diakon)', NULL, 16),
('Hari-H Sebelum Ibadah', 'Petugas ushers sudah stand-by.', NULL, 17),
('Hari-H Sebelum Ibadah', 'Apakah seluruh area sudah bersih (lantai 1 dan 2).', NULL, 18),
('Hari-H Sebelum Ibadah', 'Nyalakan AC sesuai kebutuhan (lantai 1 & 2).', NULL, 19),
('Hari-H Sebelum Ibadah', 'Pastikan Kamera Streaming dengan baterai full.', NULL, 20),
('Hari-H Sebelum Ibadah', 'Pastikan Komputer Streaming menyala.', NULL, 21),
('Hari-H Sebelum Ibadah', 'Software streaming sudah siap dijalankan.', NULL, 22),
('Hari-H Sebelum Ibadah', 'Link streaming diuji 15 menit sebelum ibadah mulai.', NULL, 23),
('Hari-H Sebelum Ibadah', 'Microfon hidup dan baterai cukup.', NULL, 24),
('Hari-H Sebelum Ibadah', 'Semua speaker monitor berfungsi.', NULL, 25),
('Hari-H Sebelum Ibadah', 'Semua alat musik siap digunakan.', NULL, 26),
('Hari-H Sebelum Ibadah', 'checksound Keyboard.', NULL, 27),
('Hari-H Sebelum Ibadah', 'checksound Gitar Listrik.', NULL, 28),
('Hari-H Sebelum Ibadah', 'checksound Bass.', NULL, 29),
('Hari-H Sebelum Ibadah', 'checksound Drum.', NULL, 30),
('Hari-H Sebelum Ibadah', 'Pastikan Easy Worship script sudah ter-update.', NULL, 31),
('Hari-H Sebelum Ibadah', 'sudah berisi lagu-lagu dari WL.', NULL, 32),
('Hari-H Sebelum Ibadah', 'sudah ada Bacaan Bertanggapan.', NULL, 33),
('Hari-H Sebelum Ibadah', 'sudah ada Doa Syafaat.', NULL, 34),
('Hari-H Sebelum Ibadah', 'konfirmasi urutan ibadah ke WL', NULL, 35),
('Hari-H Sebelum Ibadah', 'sudah ada Bahan Khotbah.', NULL, 36),
('Hari-H Sebelum Ibadah', 'File Video Warta Jemaat sudah tersedia di Laptop Multimedia.', NULL, 37),
('Hari-H Sebelum Ibadah', 'File Video Warta Jemaat sudah tersedia di Komputer Streaming.', NULL, 38),
('Hari-H Sebelum Ibadah', 'TV berfungsi dengan baik.', NULL, 39),
('Hari-H Sebelum Ibadah', 'Pengkhotbah sudah hadir.', NULL, 40),
('Hari-H Sebelum Ibadah', 'WL sudah hadir.', NULL, 41),
('Hari-H Sebelum Ibadah', 'Singer sudah hadir.', NULL, 42),
('Hari-H Sebelum Ibadah', 'Pemusik sudah hadir.', NULL, 43),
('Hari-H Sebelum Ibadah', 'Multimedia sudah hadir.', NULL, 44),
('Hari-H Sebelum Ibadah', 'Streaming sudah hadir.', NULL, 45),
('Hari-H Sebelum Ibadah', 'Soundman sudah hadir.', NULL, 46);

-- Update the checklist_responses table to match the new structure
ALTER TABLE checklist_responses MODIFY COLUMN notes TEXT;

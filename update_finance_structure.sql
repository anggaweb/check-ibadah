-- Ubah struktur tabel finance_collection untuk menggunakan INT alih-alih DECIMAL
ALTER TABLE finance_collection 
    MODIFY sunday_school INT DEFAULT 0,
    MODIFY general_service INT DEFAULT 0,
    MODIFY tithe INT DEFAULT 0,
    MODIFY diaconia INT DEFAULT 0,
    MODIFY other_offering INT DEFAULT 0,
    MODIFY total_amount INT DEFAULT 0;

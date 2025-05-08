-- Create finance_collection table
CREATE TABLE IF NOT EXISTS finance_collection (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    sunday_school DECIMAL(15,2) DEFAULT 0,
    general_service DECIMAL(15,2) DEFAULT 0,
    tithe DECIMAL(15,2) DEFAULT 0,
    diaconia DECIMAL(15,2) DEFAULT 0,
    other_offering DECIMAL(15,2) DEFAULT 0,
    other_offering_description VARCHAR(255) NULL,
    total_amount DECIMAL(15,2) DEFAULT 0,
    total_attendance INT DEFAULT 0,
    counter1_name VARCHAR(100) NULL,
    counter1_signature LONGTEXT NULL,
    counter2_name VARCHAR(100) NULL,
    counter2_signature LONGTEXT NULL,
    counter3_name VARCHAR(100) NULL,
    counter3_signature LONGTEXT NULL,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

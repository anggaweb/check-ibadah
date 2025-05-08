<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "church_checklist";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$checklists_table = "CREATE TABLE IF NOT EXISTS checklists (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'draft',
    signature LONGTEXT NULL,
    responsible_person VARCHAR(255) NULL,
    remark TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

$checklist_items_table = "CREATE TABLE IF NOT EXISTS checklist_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT(11) NOT NULL,
    category VARCHAR(100) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    status TINYINT(1) DEFAULT 0,
    notes TEXT,
    is_parent TINYINT(1) DEFAULT 0,
    parent_id INT(11) NULL,
    FOREIGN KEY (checklist_id) REFERENCES checklists(id) ON DELETE CASCADE
)";

$comments_table = "CREATE TABLE IF NOT EXISTS comments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    comment TEXT NOT NULL,
    is_global TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (checklist_id) REFERENCES checklists(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

$global_chat_table = "CREATE TABLE IF NOT EXISTS global_chat (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

$conn->query($users_table);
$conn->query($checklists_table);
$conn->query($checklist_items_table);
$conn->query($comments_table);
$conn->query($global_chat_table);

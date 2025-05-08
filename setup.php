<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS church_checklist";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db("church_checklist");

// Function to execute SQL and display result
function executeSQL($conn, $sql, $successMessage)
{
    if ($conn->query($sql) === TRUE) {
        echo "$successMessage<br>";
        return true;
    } else {
        echo "Error: " . $conn->error . "<br>";
        return false;
    }
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
executeSQL($conn, $sql, "Table 'users' created successfully");

// Create checklists table
$sql = "CREATE TABLE IF NOT EXISTS checklists (
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
executeSQL($conn, $sql, "Table 'checklists' created successfully");

// Create checklist_items table
$sql = "CREATE TABLE IF NOT EXISTS checklist_items (
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
executeSQL($conn, $sql, "Table 'checklist_items' created successfully");

// Create comments table
$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    comment TEXT NOT NULL,
    is_global TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (checklist_id) REFERENCES checklists(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
executeSQL($conn, $sql, "Table 'comments' created successfully");

// Create global_chat table
$sql = "CREATE TABLE IF NOT EXISTS global_chat (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
executeSQL($conn, $sql, "Table 'global_chat' created successfully");

// Create chat_messages table
$sql = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
executeSQL($conn, $sql, "Table 'chat_messages' created successfully");

// Create reviews table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    review TEXT NOT NULL,
    rating INT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
executeSQL($conn, $sql, "Table 'reviews' created successfully");

// Create finance_collection table
$sql = "CREATE TABLE IF NOT EXISTS finance_collection (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    sunday_school INT DEFAULT 0,
    general_service INT DEFAULT 0,
    tithe INT DEFAULT 0,
    diaconia INT DEFAULT 0,
    other_offering INT DEFAULT 0,
    other_offering_description VARCHAR(255) NULL,
    total_amount INT DEFAULT 0,
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
)";
executeSQL($conn, $sql, "Table 'finance_collection' created successfully");

// Create checklist_template table
$sql = "CREATE TABLE IF NOT EXISTS checklist_template (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    is_parent TINYINT(1) DEFAULT 0,
    parent_id INT(11) NULL
)";
executeSQL($conn, $sql, "Table 'checklist_template' created successfully");

// Create admin user if not exists
$admin_username = "admin";
$admin_email = "admin@gkpigriyapermata.org";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);

// Check if admin user already exists
$sql = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (username, email, password) VALUES ('$admin_username', '$admin_email', '$admin_password')";
    executeSQL($conn, $sql, "Admin user created successfully");
} else {
    echo "Admin user already exists<br>";
}

// Insert default checklist template items for Ruang Penyambutan Lantai Dasar
$categories = [
    'Ruang Penyambutan Lantai Dasar' => [
        'Meja penyambutan bersih dan rapi',
        'Amplop Persembahan sudah dipersiapkan (perpuluhan / diakon)',
        'Petugas ushers sudah stand-by',
        'Apakah seluruh area sudah bersih (lantai 1 dan 2)',
        'Nyalakan AC sesuai kebutuhan (lantai 1 & 2)'
    ],
    'Ruang Ibadah' => [
        'TV berfungsi dengan baik',
        'Semua speaker monitor berfungsi',
        'Microfon hidup dan baterai cukup'
    ],
    'Streaming Setup' => [
        'Pastikan Kamera Streaming dengan baterai full',
        'Pastikan Komputer Streaming menyala',
        'Software streaming sudah siap dijalankan',
        'Link streaming diuji 15 menit sebelum ibadah mulai',
        'File Video Warta Jemaat sudah tersedia di Komputer Streaming'
    ],
    'Petugas' => [
        'Pengkhotbah sudah hadir',
        'WL sudah hadir',
        'Singer sudah hadir',
        'Pemusik sudah hadir',
        'Streaming sudah hadir',
        'Multimedia sudah hadir',
        'Soundman sudah hadir'
    ]
];

// Check if checklist_template is empty
$result = $conn->query("SELECT COUNT(*) as count FROM checklist_template");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    echo "Inserting default checklist template items...<br>";

    // Insert basic categories
    foreach ($categories as $category => $items) {
        foreach ($items as $item) {
            $sql = "INSERT INTO checklist_template (category, item_name, is_parent, parent_id) 
                    VALUES ('$category', '$item', 0, NULL)";
            $conn->query($sql);
        }
    }

    // Insert Sound System parent item
    $sql = "INSERT INTO checklist_template (category, item_name, is_parent, parent_id) 
            VALUES ('Sound System', 'Semua alat musik siap digunakan', 1, NULL)";
    if ($conn->query($sql)) {
        $parent_id = $conn->insert_id;

        // Insert Sound System child items
        $sound_items = [
            'checksound Keyboard',
            'checksound Gitar Listrik',
            'checksound Bass',
            'checksound Drum'
        ];

        foreach ($sound_items as $item) {
            $sql = "INSERT INTO checklist_template (category, item_name, is_parent, parent_id) 
                    VALUES ('Sound System', '$item', 0, $parent_id)";
            $conn->query($sql);
        }
    }

    // Insert Multimedia parent item
    $sql = "INSERT INTO checklist_template (category, item_name, is_parent, parent_id) 
            VALUES ('Multimedia', 'Pastikan Easy Worship script sudah ter-update', 1, NULL)";
    if ($conn->query($sql)) {
        $parent_id = $conn->insert_id;

        // Insert Multimedia child items
        $multimedia_items = [
            'sudah berisi lagu-lagu dari WL',
            'sudah ada Bacaan Bertanggapan',
            'sudah ada Doa Syafaat',
            'konfirmasi urutan ibadah ke WL',
            'sudah ada Bahan Khotbah'
        ];

        foreach ($multimedia_items as $item) {
            $sql = "INSERT INTO checklist_template (category, item_name, is_parent, parent_id) 
                    VALUES ('Multimedia', '$item', 0, $parent_id)";
            $conn->query($sql);
        }
    }

    echo "Default checklist template items inserted successfully<br>";
} else {
    echo "Checklist template items already exist<br>";
}

echo "<br>Setup completed! <a href='index.php'>Go to homepage</a>";

$conn->close();
